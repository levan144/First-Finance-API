<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;

use Laravel\Nova\Http\Requests\NovaRequest;

class BankAccount extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\BankAccount>
     */
    public static $model = \App\Models\BankAccount::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    // public static $title = 'id';
    
     /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
     public function title() // Add this method
    {
        return $this->account_name;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
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

            BelongsTo::make('UserBank')
                ->sortable()
                ->rules('required'),

            BelongsTo::make('Currency')
                ->sortable()
                ->rules('required'),

            Text::make('Account Name')
                ->sortable()
                ->rules('required'),

            Text::make('IBAN')
                ->sortable()
                ->rules('required'),
                
            Text::make('BIC')
                ->sortable()
                ->rules('required'),
            
            Currency::make('Balance')
            ->sortable()
           
            ->displayUsing(function ($value) {
                $symbol = optional($this->currency)->symbol ?: '$';
                return $symbol . number_format($value, 2);
            })
            ->rules('required'),
            // Currency::make('Balance')
            //     ->currency('USD')
            //     ->sortable(),
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
