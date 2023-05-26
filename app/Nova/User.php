<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Place;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\Boolean;
use Stepanenko3\NovaJson\JSON;
use Alexwenzel\DependencyContainer\HasDependencies;
use Alexwenzel\DependencyContainer\DependencyContainer;
use App\Nova\BankAccount;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasManyThrough;
use Laravel\Nova\Fields\Currency as CurrencyField;


class User extends Resource
{
    
    use HasDependencies;
    
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Gravatar::make()->maxWidth(50),
            
            // Hidden::make('company_id','company_id'),
            Boolean::make(__('Verified'), 'verified_at')
                ->trueValue(now())
                ->falseValue(null)->withMeta(['value' => $this->verified_at !== null ? true : false]),
                
            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make(__('Email'), 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),
                
            Text::make(__('Role'), function () {
                if($this->isSuperAdmin()){
                    return sprintf('%s', '');
                }
                return sprintf('%s', 'Company');
            }),
           
            Password::make(__('Password'), 'password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),
            
            //Dependencies Container not working as expected on BelongsTo, so i add Second BelongsTo Field and its working now. 
            // BelongsTo::make('LegalForm')->exceptOnForms()->hideFromDetail()->hideFromIndex(),
            Text::make(__('Legal Form'), 'legal_form'),
            Text::make(__('Phone number'), 'phone'),
            
            // BelongsTo::make('legalForm')->searchable(),
            Date::make(__('Registration Date'),'registration_date'),
            Text::make(__('Registration number'), 'registration_number'),
            
            CurrencyField::make(__('Balance Due'), 'balance_due')
                ->currency('GEL')
                ->sortable(),
            
            HasMany::make(__('Representatives'), 'representatives', \App\Nova\LegalRepresentative::class),
            HasMany::make('Fees'),
            // BelongsTo::make('Representative')->nullable(),
            $this->addressFields(),
            $this->registrationAddressFields(),
            HasMany::make('Documents'),

            HasMany::make('Bank Accounts', 'bankAccounts', BankAccount::class),
            HasMany::make('UserBanks'),
            HasMany::make('Transactions', 'transactions', Transaction::class),
            
            MorphToMany::make('Roles', 'roles', \Sereny\NovaPermissions\Nova\Role::class),
            MorphToMany::make('Permissions', 'permissions', \Sereny\NovaPermissions\Nova\Permission::class),
            
        ];
    }
    
    /**
     * Get the address fields for the resource.
     *
     * @return \Illuminate\Http\Resources\MergeValue
     */
    protected function addressFields()
    {
        return $this->merge([
            JSON::make('Address', [
                Place::make('Address Line 1', 'line_1')->hideFromIndex(),
                Text::make('Address Line 2', 'line_2')->hideFromIndex(),
                Text::make('City', 'city')->hideFromIndex(),
                Text::make('Zip Code', 'zip')->hideFromIndex(),
                Country::make('Country', 'country')->hideFromIndex(),
            ])
        ]);
    }
    
    /**
     * Get the address fields for the resource.
     *
     * @return \Illuminate\Http\Resources\MergeValue
     */
    protected function registrationAddressFields()
    {
        return $this->merge([
            JSON::make('Registration Address', [
                Place::make('Address Line 1', 'line_1')->hideFromIndex(),
                Text::make('Address Line 2', 'line_2')->hideFromIndex(),
                Text::make('City', 'city')->hideFromIndex(),
                Text::make('Zip Code', 'zip')->hideFromIndex(),
                Country::make('Country', 'country')->hideFromIndex(),
            ])
        ]);
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
                new Filters\UserType,
            ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [
            
            ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
    
    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public static function indexQuery(NovaRequest $request, $query)
    // {
    //     return $query->where('company_id', null);
    // }
}
