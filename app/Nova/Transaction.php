<?php

namespace App\Nova;

use App\Nova\Filters\TransactionStatusFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency as CurrencyField;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Transaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Transaction::class;

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
        'id', 'recipient_name', 'sender_iban', 'recipient_iban',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Sender', 'user', User::class)
                ->sortable()
                ->rules('required'),

            BelongsTo::make('Bank Account', 'bankAccount', BankAccount::class)
                ->sortable()
                ->rules('required'),

            Select::make('Recipient Type', 'recipient_type')
                ->options([
                    'company' => 'Company',
                    'individual' => 'Individual',
                ])
                ->rules('required'),

            Text::make('Recipient Name')
                ->sortable()
                ->rules('required'),

            Text::make('Sender IBAN', 'sender_iban')
                ->sortable()
                ->rules('required'),

            Text::make('Recipient IBAN', 'recipient_iban')
                ->sortable()
                ->rules('required'),
                
            Text::make(__('Beneficiary Country Code'), 'beneficiary_country_code'),
            Text::make(__('Beneficiary Address'), 'beneficiary_address'),
            Text::make(__('Bank Name'), 'bank_name'),
            Text::make(__('Bank Code'), 'bank_code'),
            Text::make(__('Intermediary Bank Name'), 'intermediary_bank_name'),
            Text::make(__('Intermediary Bank Code'), 'intermediary_bank_code'),

            BelongsTo::make('Currency')
                ->sortable()
                ->rules('required'),

            CurrencyField::make('Amount')
                ->currency('USD')
                ->sortable()
                ->rules('required'),

            CurrencyField::make('Fee')
                ->currency('GEL')
                ->sortable(),
            
            CurrencyField::make(__('Bank Fee'),'bank_fee')
                ->currency('USD')
                ->sortable(),
            
            Select::make('Type')
                ->options([
                    'transfer' => 'Transfer',
                    'deposit' => 'Deposit',
                    'exchange' => 'Exchange',
                ])
                ->rules('required'),

            Select::make('Status')->options([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Rejected' => 'Rejected',
            ])->displayUsingLabels()->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new TransactionStatusFilter,
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
     * Determine if this resource is available for navigation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return true;
    }
    
    /**
     * Perform any final formatting of the given fields before being displayed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $fields
     * @return array
     */
    public function formatFields(Request $request, $fields)
    {
        // If the transaction status is 'Rejected', disable the fields
        if ($this->status == 'Rejected') {
            foreach ($fields as &$field) {
                $field->readonly(true);
            }
        }
    
        return $fields;
    }
    
    /**
     * Update the model instance represented by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(NovaRequest $request, $model)
    {
        // If the transaction status is 'Rejected', don't update the model
        if ($model->status == 'Rejected') {
            return back()->with('message', 'Cannot update a rejected transaction.');
        }
    
        parent::update($request, $model);
    }
}
