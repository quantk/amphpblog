<?php


namespace App\Cli;


use Amp\Loop;
use App\User\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAdminCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:create-admin';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Loop::run(function () use ($output) {
            /** @var User|null $adminExists */
            $adminExists = yield User::builder()
                ->select()
                ->where('username = :username')
                ->bindValue('username', 'admin')
                ->first();
            if ($adminExists) {
                $output->writeln("{$adminExists->id}:{$adminExists->username}");
                Loop::stop();
            }
            $user           = User::create();
            $user->username = 'admin';
            $user->password = 'admin';
            yield $user->save();

            $output->writeln("{$user->id}:{$user->username}");
            Loop::stop();
        });

    }
}