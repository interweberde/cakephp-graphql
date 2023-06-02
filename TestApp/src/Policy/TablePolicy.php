<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Interweber\GraphQL\Policy\TablePolicy as BaseTablePolicy;
use TestApp\Model\Entity\User;

/**
 * @template-extends BaseTablePolicy<User>
 */
abstract class TablePolicy extends BaseTablePolicy {
}
