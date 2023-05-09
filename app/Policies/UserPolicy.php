<?php
namespace App\Policies;

// use Eminiarts\NovaPermissions\Policies\Policy;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
class UserPolicy
{
    use HandlesAuthorization;
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'users';
    
    /**
     * Determine whether the user can view any posts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->can('view users')) {
            return true;
        }
    }
    
    /**
     * Determine whether the user can view detailed page.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        if ($user->can('view users')) {
            return true;
        }
    }
    
    /**
     * Determine whether the user can update the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function update(User $user)
    {
        
        if ($user->can('manage users')) {
            return true;
        }
    }
    
    /**
     * Determine whether the user can delete the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function delete(User $user)
    {
       
        if ($user->can('forceDelete users')) {
            return true;
        }
    }
}