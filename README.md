## Install
composer require Jsadways/scopFilter

## Edit Model
use Jsadways\ScopeFilter\ScopeFilterTrait;

class User extends Model
{

    ...
    use ScopeFilterTrait;
    ...

}
