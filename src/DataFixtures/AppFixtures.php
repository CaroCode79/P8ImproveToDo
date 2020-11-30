<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * encoder.
     *
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $date = (new DateTimeImmutable())->sub(new DateInterval('PT60H'));

        // 51 tasks, 25 are done
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
            $manager->persist($task);
        }

        // User with only ROLE_USER
        $user = new User();
        $user->setUsername('user1');
        $user->setPassword($this->encoder->encodePassword($user, 'password'));
        $user->setEmail('user1@email.com');
        $manager->persist($user);

        // User with only ROLE_USER
        $user = new User();
        $user->setUsername('user2');
        $user->setPassword($this->encoder->encodePassword($user, 'password'));
        $user->setEmail('user2@email.com');
        $manager->persist($user);

        // User with ROLE_ADMIN
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword($this->encoder->encodePassword($user, 'password'));
        $user->setEmail('admin@email.com');
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        $manager->flush();
    }
}
