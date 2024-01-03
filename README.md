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

## Example

class UserController extends Controller
{
    
    $filter = [
        'status_eq' => 1,
        'keyword_or => [
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
