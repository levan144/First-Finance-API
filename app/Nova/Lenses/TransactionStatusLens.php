<?php

namespace App\Nova\Lenses;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Http\Requests\LensRequest;

class TransactionStatusLens extends Lens
{
    /**
     * Get the displayable name of the lens.
     *
     * @return string
     */
    public function name()
    {
        return 'Transactions by Status';
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Select::make('Status')
                ->options([
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                ])
                ->displayUsingLabels()
                ->nullable()
                ->default(null)
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new TransactionStatusFilter,
        ];
    }

    /**
     * Perform any custom query for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function query(LensRequest $request, $query)
    {
        return $query;
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'transaction-status-lens';
    }
    
    /**
     * Save a new model and return the model instance.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saveInstance(NovaRequest $request, $model)
    {
        $isNewModel = !$model->exists;
    
        // If the transaction status is 'Rejected', don't save the model
        if ($model->status == 'Rejected') {
            return back()->with('message', 'Cannot save a rejected transaction.');
        }
    
        parent::saveInstance($request, $model);
    
        // If it's a new model and the status is 'Approved', process the transaction
        if ($isNewModel && $model->status == 'Approved') {
            $this->processTransaction($model);
        }
    }
    
    /**
     * Process the transaction based on its type.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    protected function processTransaction(Transaction $transaction)
    {
        switch ($transaction->type) {
            case 'transfer':
                $transaction->processTransfer();
                break;
            case 'exchange':
                $transaction->processExchange();
                break;
            case 'deposit':
                $transaction->processDeposit();
                break;
        }
    }
}
