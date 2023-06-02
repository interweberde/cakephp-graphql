<?php
declare(strict_types=1);

namespace TestApp\GraphQL\Controller;

use GraphQL\Type\Definition\ResolveInfo;
use Interweber\GraphQL\Classes\BaseController;
use TestApp\Model\Entity\Baz;
use TestApp\Model\Entity\User;
use TestApp\Model\Table\BazsTable;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @template-extends BaseController<BazsTable, Baz>
 */
class BazsController extends BaseController {
	#[Query]
	public function getBaz(
		ResolveInfo $resolveInfo,
		#[InjectUser]
		User $identity,
		ID $id
	): Baz {
		return $this->_fetchEntityByPK($resolveInfo, $identity, $id);
	}
}
