<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Interweber\GraphQL\Policy\EntityPolicy as BaseEntityPolicy;
use TestApp\Model\Entity\User;

/**
 * @template-extends BaseEntityPolicy<User>
 */
abstract class EntityPolicy extends BaseEntityPolicy {
}
