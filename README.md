## Install
composer require jsadways/scopefilter

## Use Keys
* keyword
* or
* and
* OrRelation_or
* AndRelation_or
* OrRelation_and
* AndRelation_and
* column : columnName_operator => value

## Edit Model
use Jsadways\ScopeFilter\ScopeFilterTrait;

```
class User extends Model
{

    ...
    use ScopeFilterTrait;
    ...

}
```

## Example

```
class UserController extends Controller
{
    
    $filter = [
        'keyword' => 'johnny',
        'status_eq' => 1,
        'or' => [
            'tel_k' => '0922',
            'titke_k => 'super'
        ],
        'andRelation_or' => [
            'education' => [
                'school_name_k' => 'National'
            ]
        ]
    ];
    $user_list = User::filter($filter)->get();
}
```
