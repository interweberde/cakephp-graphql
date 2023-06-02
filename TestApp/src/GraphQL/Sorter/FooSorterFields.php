<?php
declare(strict_types=1);

namespace TestApp\GraphQL\Sorter;

enum FooSorterFields {
	case DATE_ASC;
	case DATE_DESC;

	case ID_ASC;
	case ID_DESC;
}
