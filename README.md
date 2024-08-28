## Install
composer require jsadways/scopefilter

## Edit Model
use Jsadways\ScopeFilter\ScopeFilterTrait;

class User extends Model
{

    ...
    use ScopeFilterTrait;
    ...

}

## Example 1 use custom column search keyword

class UserController extends Controller
{
    
    $filter = [
        'status_eq' => 1,
        'keyword_or' => [
            'name_k' => 'johnny',
            'name_en => 'johnny'
        ],
        'title_or' => [
            'sub_title_k' => 'master',
            'title_company_k' => 'master'
        ]
    ];
    $user_list = User::filter($filter)->get();
}

## Example 2 use default fillable column and relation fillable column search keyword

class UserController extends Controller
{

    $filter = [
        'status_eq' => 1,
        'keyword' => 'johnny',
        'title_or' => [
            'sub_title_k' => 'master',
            'title_company_k' => 'master'
        ]
    ];
    $user_list = User::filter($filter)->get();
}

## Example 3 search relation table column

class UserController extends Controller
{

    $filter = [
        'status_eq' => 1,
        'relation_@' => [
            'education' => [
                'school_name_k' => 'National'
            ]
        ]
    ];
    $user_list = User::filter($filter)->get();
}
