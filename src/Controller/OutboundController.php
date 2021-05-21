<?php

namespace App\Controller;

use App\Entity\Inbound;
use App\Entity\Outbound;
use App\Repository\InboundRepository;
use App\Repository\OutboundRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class OutboundController extends AbstractController
{
    private const MAX_OUTBOUND = 20;
    private const FORM_TYPES_NAME = ['form.type.raw', 'form.type.json'];

    private Security $security;
    private FormFactoryInterface $formFactory;
    private InboundRepository $inboundRepository;
    private OutboundRepository $outboundRepository;

    public function __construct(
        Security $security,
        FormFactoryInterface $formFactory,
        InboundRepository $inboundRepository,
        OutboundRepository $outboundRepository
    )
    {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->inboundRepository = $inboundRepository;
        $this->outboundRepository = $outboundRepository;
    }

    /**
     * @Route("/outbound", name="outbound")
     */
    public function index(Request $request): Response
    {
        $username = $this->security->getUser()->getUsername();

        $inbounds = $this->inboundRepository->findBy([
            'username' => $username,
        ]);

        $this->saveData(
            $inbounds,
            $request->request->get('form') ?? []
        );
        $form = $this->buildForm($inbounds)->createView();

        return $this->render('outbound/index.html.twig', [
            'form' => $form,
            'maxIndex' => self::MAX_OUTBOUND - 1,
        ]);
    }

    private function saveData(array $inbounds, array $formData): void
    {
        if (count($formData) === 0) {
            return;
        }

        for ($i = 0; $i < self::MAX_OUTBOUND; $i++) {
            $id = $formData['id_' . $i];
            $inboundId = (int)$formData['inbound_' . $i];
            $name = $formData['name_' . $i];
            $url = $formData['url_' . $i];
            $type = $formData['type_' . $i];
            $body = $formData['body_' . $i];

            if ($id !== '' && $name === '') {
                continue;
            }

            $inbound = array_filter(
                $inbounds,
                static fn(Inbound $innerInbound): bool => $innerInbound->getId() === $inboundId
            );
            $inbound = reset($inbound);

            if ($inbound === null) {
                continue;
            }

            if ($id === '' && $name === '') {
                continue;
            }

            if ($id === '' && $name !== '') {
                $outbound = (new Outbound())
                    ->setName($name)
                    ->setUrl($url)
                    ->setType($type)
                    ->setBody($body)
                    ->setInbound($inbound);

                $this->outboundRepository->insert($outbound);

                continue;
            }

            $outbound = $this->outboundRepository->find((int)$id);
            $outbound
                ->setName($name)
                ->setInbound($inbound)
                ->setUrl($url)
                ->setType($type)
                ->setBody($body)
                ->setUpdatedAt(new DateTimeImmutable());
            $this->outboundRepository->update($outbound);
        }
    }

    private function buildForm(array $inbounds): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class);

        $outbounds = [];
        $choices = [];
        foreach ($inbounds as $inbound) {
            assert($inbound instanceof Inbound);

            $outbounds = [
                ...$outbounds,
                ...$inbound->getOutbounds()->toArray(),
            ];

            $choices[$inbound->getName()] = $inbound->getId();
        }

        $count = 0;
        foreach ($outbounds as $outbound) {
            assert($outbound instanceof Outbound);

            $this->createFormElementGroup($count, $choices, $builder, [
                'id' => $outbound->getId(),
                'inbound' => $outbound->getInbound()->getId(),
                'name' => $outbound->getName(),
                'url' => $outbound->getUrl(),
                'type' => $outbound->getType(),
                'body' => $outbound->getBody(),
            ]);

            $count++;
        }

        if ($count < self::MAX_OUTBOUND) {
            for ($i = $count; $i < self::MAX_OUTBOUND; $i++) {
                $this->createFormElementGroup($i, $choices, $builder, []);
            }
        }

        $builder->add('submit_top', SubmitType::class, [
            'label' => 'form.submit',
            'attr' => ['class' => 'btn btn-success'],
        ]);

        $builder->add('submit_bottom', SubmitType::class, [
            'label' => 'form.submit',
            'attr' => ['class' => 'btn btn-success'],
        ]);

        return $builder->getForm();
    }

    private function createFormElementGroup(int $i, array $choices, FormBuilderInterface $formBuilder, array $data): void
    {
        $typeChoice = array_combine(
            self::FORM_TYPES_NAME,
            Outbound::ALLOWED_TYPES
        );

        $formBuilder
            ->add('id_' . $i, HiddenType::class, [
                'data' => $data['id'] ?? null,
            ])
            ->add('inbound_' . $i, ChoiceType::class, [
                'label' => 'form.inbound',
                'data' => $data['inbound'] ?? null,
                'required' => false,
                'choices' => $choices,
            ])
            ->add('name_' . $i, TextType::class, [
                'label' => 'form.name',
                'data' => $data['name'] ?? null,
                'required' => false,
            ])
            ->add('url_' . $i, TextType::class, [
                'label' => 'form.url',
                'data' => $data['url'] ?? null,
                'required' => false,
            ])
            ->add('type_' . $i, ChoiceType::class, [
                'label' => 'form.type.main',
                'data' => $data['type'] ?? null,
                'required' => false,
                'choices' => $typeChoice,
            ])
            ->add('body_' . $i, TextareaType::class, [
                'label' => 'form.body',
                'data' => $data['body'] ?? null,
                'required' => false,
            ]);
    }
}
