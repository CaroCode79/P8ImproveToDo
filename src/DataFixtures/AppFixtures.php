<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // One User with only ROLE_USER
        $user1 = new User();
        $user1->setUsername('user1');
        $user1->setPlainPassword('password');
        $user1->setEmail('user1@email.com');
        $manager->persist($user1);

        // One User with only ROLE_USER
        $user2 = new User();
        $user2->setUsername('user2');
        $user2->setPlainPassword('password');
        $user2->setEmail('user2@email.com');
        $manager->persist($user2);

        // User with ROLE_ADMIN
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setPlainPassword('password');
        $admin->setEmail('admin@email.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Anonymous user with only ROLE_USER
        $anonymous = new User();
        $anonymous->setUsername('Anonymous');
        $anonymous->setPlainPassword('password');
        $anonymous->setEmail('anonymous@email.com');
        $manager->persist($anonymous);

        // 51 tasks, 25 are done
        $date = (new DateTimeImmutable())->sub(new DateInterval('PT60H'));
        for ($i = 1; $i <= 51; ++$i) {
            $task = new Task();
            $task->setTitle('Tâche n°'.$i);
            $task->setContent('Texte du contenu de la tâche n°'.$i);
            $task->setCreatedAt($date);
            $task->setUpdatedAt($date);
            $duration = 'PT'.(string) $i.'H';
            $task->setUpdatedAt($task->getUpdatedAt()->add(new DateInterval($duration)));
            if (0 === $i % 2) {
                $task->toggle(true);
            }
            if (10 < $i && $i <= 20) {
                $task->setUser($user1);
            }
            if (20 < $i && $i <= 30) {
                $task->setUser($admin);
            }
            if (40 < $i) {
                $task->setUser($user2);
            }
            $manager->persist($task);
        }

        $manager->flush();
    }
}
