<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\DateTime;

class Fee extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Fee>
     */
    public static $model = \App\Models\Fee::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];
    
    public static function authorizedToCreate(Request $request)
    {
        return false; // disable creating new resources
    }
    
    public static function availableForNavigation(Request $request)
    {
        return false; // hide resource in navigation
    }

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

            BelongsTo::make(__('User'), 'user', 'App\Nova\User'),

            Select::make(__('Transaction Type'))->options([
                'deposit' => __('Deposit'),
                'transfer' => __('Transfer'),
                'exchange' => __('Exchange'),
            ])->displayUsingLabels(),

            Number::make(__('Amount'), 'amount')->displayUsing(function ($amount) {
                return $amount . '%';
            })->sortable(),

            DateTime::make(__('Created At'))->onlyOnDetail(),
            DateTime::make(__('Updated At'))->onlyOnDetail(),
        ];
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
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
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
}
