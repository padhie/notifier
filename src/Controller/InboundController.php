<?php

namespace App\Controller;

use App\Entity\Inbound;
use App\Repository\InboundRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class InboundController extends AbstractController
{
    private const MAX_INBOUND = 5;

    private Security $security;
    private FormFactoryInterface $formFactory;
    private InboundRepository $inboundRepository;

    public function __construct(
        Security $security,
        FormFactoryInterface $formFactory,
        InboundRepository $inboundRepository
    ) {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->inboundRepository = $inboundRepository;
    }

    /**
     * @Route("/inbound", name="inbound")
     */
    public function index(Request $request): Response
    {
        $username = $this->security->getUser()->getUsername();

        $this->saveData(
            $username,
            $request->request->get('form') ?? []
        );
        $form = $this->buildForm($username)->createView();

        return $this->render('inbound/index.html.twig', [
            'form' => $form,
            'maxIndex' => self::MAX_INBOUND - 1,
        ]);
    }

    private function saveData(string $username, array $formData): void
    {
        if (count($formData) === 0) {
            return;
        }

        for ($i=0; $i<self::MAX_INBOUND; $i++) {
            $id = $formData['id_' . $i];
            $name = $formData['name_' . $i];

            if ($id !== '' && $name === '') {
                $this->inboundRepository->deleteById($id);

                continue;
            }

            if ($id === '' && $name !== '') {
                $inbound = (new Inbound())
                    ->setUsername($username)
                    ->setName($name)
                    ->setHash(uniqid());

                $this->inboundRepository->insert($inbound);

                continue;
            }

            $inbound = $this->inboundRepository->find((int) $id);
            $inbound->setName($name)
                ->setUpdatedAt(new DateTimeImmutable());
            $this->inboundRepository->update($inbound);
        }
    }

    private function buildForm(string $username): FormInterface
    {
        $inbounds = $this->inboundRepository->findBy([
            'username' => $username,
        ]);

        $builder = $this->formFactory->createBuilder(FormType::class);

        $count = 0;
        foreach ($inbounds as $inbound) {
            $this->createFormElementGroup($count, $builder, [
                'id' => $inbound->getId(),
                'name' => $inbound->getName(),
                'hash' => $inbound->getHash(),
            ]);

            $count++;
        }

        if ($count < self::MAX_INBOUND) {
            for ($i=$count; $i<self::MAX_INBOUND; $i++) {
                $this->createFormElementGroup($i, $builder, []);
            }
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'form.submit',
            'attr' => ['class' => 'btn btn-success'],
        ]);

        return $builder->getForm();
    }

    private function createFormElementGroup(int $i, FormBuilderInterface $formBuilder, array $data): void
    {
        $formBuilder
            ->add('id_' . $i, HiddenType::class, [
                'data' => $data['id'] ?? null,
            ])
            ->add('name_' . $i, TextType::class, [
                'label' => 'form.name',
                'data' => $data['name'] ?? null,
                'required' => false,
            ])
            ->add('hash_' . $i, TextType::class, [
                'label' => 'form.hash',
                'data' => $data['hash'] ?? null,
                'required' => false,
                'attr' => ['readOnly' => true],
            ]);
    }
}
