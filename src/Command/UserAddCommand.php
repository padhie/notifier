<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserAddCommand extends Command
{
    protected static $defaultName = 'user:add';

    private UserPasswordEncoderInterface $passwordEncoder;
    private UserRepository $userRepository;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository
    ) {
        parent::__construct(null);

        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this->setDescription('Add a new user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');

        $userQuestion = new Question('What is the name, of the user?');

        $passQuestion = new Question('What is the password?');
        $passQuestion->setHidden(true);
        $passQuestion->setHiddenFallback(true);

        $username = null;
        $password = null;
        $check = false;

        while($check === false) {
            $username = $helper->ask($input, $output, $userQuestion);
            $password = $helper->ask($input, $output, $passQuestion);

            if ($username === null || $password === null) {
                $io->error('Username or Password are empty.');
            } else {
                $check = true;
            }
        }

        $user = new User();
        $user->setUsername($username)
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $this->userRepository->save($user);

        $io->success('User created.');

        return Command::SUCCESS;
    }
}
