<?php

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<User>
 *
 * @method static User|Proxy createOne(array $attributes = [])
 * @method static User[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'password' => self::faker()->password(),
            'roles' => ['ROLE_USER'],
        ];
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}