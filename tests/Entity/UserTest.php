<?php

namespace App\Tests\Entity;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @internal
 */
class UserTest extends WebTestCase
{
    use FixturesTrait;

    public function testUsername(): void
    {
        $user = new User();
        $username = 'username';
        $user->setUsername($username);
        $this->assertEquals($username, $user->getUsername());
    }

    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword('password');
        self::bootKernel();
        $container = self::$container;
        /** @var UserPasswordEncoderInterface $encoder */
        $encoder = $container->get('security.user_password_encoder.generic');
        $password = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());
    }

    public function testPlainPassword(): void
    {
        $user = new User();
        $plainPassword = 'password';
        $user->setPlainPassword($plainPassword);
        $this->assertEquals($plainPassword, $user->getPlainPassword());
    }

    public function testEmail(): void
    {
        $user = new User();
        $email = 'email.test@domain.com';
        $user->setEmail($email);
        $this->assertEquals($email, $user->getEmail());
    }

    public function testCreatedAt(): void
    {
        $user = new User();
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());

        $createdAt = new DateTimeImmutable('2020-11-03');
        $user->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $user->getCreatedAt());
    }

    public function testUpdatedAt(): void
    {
        $user = new User();
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());

        $updatedAt = new DateTimeImmutable('2020-11-03');
        $user->setUpdatedAt($updatedAt);
        $this->assertEquals($updatedAt, $user->getUpdatedAt());
    }

    public function testRoles(): void
    {
        $user = new User();
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));

        $role = ['ROLE_APP'];
        $user->setRoles($role);
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));
        $this->assertTrue(in_array('ROLE_APP', $user->getRoles()));
        $this->assertEquals(['ROLE_APP', 'ROLE_USER'], $user->getRoles());
    }

    public function testAddTask(): void
    {
        $user = new User();
        $task = new Task();
        $user->addTask($task);

        $this->assertContains($task, $user->getTasks(), 'Echec de User::testAddTask()');
    }

    public function testRemoveTask(): void
    {
        $user = new User();
        $task1 = new Task();
        $task2 = new Task();
        $user->addTask($task1);
        $user->addTask($task2);
        $this->assertContains($task2, $user->getTasks());

        $user->removeTask($task2);
        $this->assertNotContains($task2, $user->getTasks(), 'Echec de User::removeTask()');
    }

    public function getValidUser(): User
    {
        return (new User())
            ->setUsername('username')
            ->setPlainPassword('password')
            ->setEmail('username@mail.com')
        ;
    }

    public function assertHasErrors(User $user, int $number = 0): void
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath().' => '.$error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidUser(): void
    {
        $this->assertHasErrors($this->getValidUser(), 0);
    }

    public function testInvalidBlankUsername(): void
    {
        $this->assertHasErrors($this->getValidUser()->setUsername(''), 2);
    }

    public function testInvalidLengthUsername(): void
    {
        $this->assertHasErrors($this->getValidUser()->setUsername('Jo'), 1);
    }

    public function testInvalidUniqueUsername(): void
    {
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);
        $this->assertHasErrors($this->getValidUser()->setUsername('user1'), 1);
    }

    public function testInvalidBlankPlainPassword(): void
    {
        $this->assertHasErrors($this->getValidUser()->setPlainPassword(''), 2);
    }

    public function testInvalidMinLengthPlainPassword(): void
    {
        $this->assertHasErrors($this->getValidUser()->setPlainPassword('000'), 1);
    }

    public function testInvalidMaxLengthPlainPassword(): void
    {
        $password = 'x';
        for ($i = 0; $i < 5000; ++$i) {
            $password .= (string) $i;
        }

        $this->assertHasErrors($this->getValidUser()->setPlainPassword($password), 1);
    }

    public function testInvalidBlankEmail(): void
    {
        $this->assertHasErrors($this->getValidUser()->setEmail(''), 1);
    }

    public function testInvalidEmail(): void
    {
        $this->assertHasErrors($this->getValidUser()->setEmail('email'), 1);
    }

    public function testInvalidUniqueEmail(): void
    {
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);
        $this->assertHasErrors($this->getValidUser()->setEmail('user1@email.com'), 1);
    }
}
