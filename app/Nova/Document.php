<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Text;
// use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;

class Document extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Document>
     */
    public static $model = \App\Models\Document::class;

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
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(Request $request){
        return [
            ID::make()->sortable(),
            Text::make(__('Name'),'name')
              ->rulesFor('en', [
                    'required',
                ])
              ->nullable()
              ->translatable(),
              
             Text::make(__('Description'),'description')
              ->translatable(),
              
            MorphTo::make('Documentable')->types([
                User::class            ]),
            File::make(__('File'), 'file_path')->disk('public'),
            
            DateTime::make(__('Expiry Date'), 'expiry_date')
                ->sortable()
                ->nullable(),
                
            Select::make(__('status'), 'status')
                ->options([
                    'Approved' => __('Approved'),
                    'Expired' => __('Expired'),
                    'Pending' => __('Pending'),
                    'Rejected' => __('Rejected'),
                ])
                ->rules('required'),

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
