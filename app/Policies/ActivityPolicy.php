<?php
namespace App\Policies;

// use Eminiarts\NovaPermissions\Policies\Policy;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
class ActivityPolicy
{
    use HandlesAuthorization;
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'activity';
    
    /**
     * Determine whether the user can view any posts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->can('view activity')) {
            return true;
        }
    }
}