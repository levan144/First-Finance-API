<!DOCTYPE html>
<html>
<head>
  <title>Invoice</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    .invoice-header {
      text-align: center;
      margin-bottom: 20px;
    }

    .invoice-body {
      margin-bottom: 20px;
    }

    .invoice-footer {
      text-align: right;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
    }

    .table th, .table td {
      border: 1px solid black;
      padding: 5px;
    }
    .table th, .table td {
  font-size: 0.7em; /* Adjust as necessary */
}
  </style>
</head>
<body>
  <div class="invoice-header">
    <img src="{{env('APP_URL')}}/public/images/firstfinance.png" alt="Company Logo">
    <h1>Invoice #{{$transaction->id}}</h1>
  </div>

  <div class="invoice-body">
      <h3><strong>{{$transaction->user->name}}</strong> </h3>
    <p><strong>Account Name:</strong> {{$transaction->bankAccount->account_name}}</p>
    <p><strong>IBAN:</strong> {{$transaction->sender_iban}}</p>
    <p><strong>BIC:</strong> {{$transaction->bankAccount->bic}}</p>

    <h2>Billing Information</h2>
    @foreach($transaction->user->address as $key => $value)
    <p>{{$value}}</p>
    @endforeach

    <h2>Transaction Details</h2>
    <table class="table">
      <thead>
        <tr>
          <th>Start Date</th>
          <th>Description</th>
          <th>Money Out</th>
          <th>Money In</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{$transaction->created_at}}</td>
          <td>{{$transaction->reference}}</td>
          <td>{{$transaction->type === 'transfer' ? $transaction->currency->symbol . ' ' . $transaction->amount : ''}}</td>
          <td>{{$transaction->type === 'deposit' ? $transaction->currency->symbol . ' ' . $transaction->amount : ''}}</td>
          
        </tr>
        <!-- More rows as necessary -->
      </tbody>
    </table>
  </div>

  <div class="invoice-footer">
    <p>Thank you for your business!</p>
  </div>
</body>
</html>
