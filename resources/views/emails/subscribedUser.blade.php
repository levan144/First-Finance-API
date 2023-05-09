<x-mail::message>
# User Subscribed

This email is to notify you that a new subscriber has been added to your mailing list. Below are the details of the subscriber:
<br><br>
<strong>Name:</strong> {{$user->name}}<br>
<strong>Email:</strong> {{$user->email}}<br>
<strong>Phone:</strong> {{$user->phone}}<br>
<br>
Thank you for your attention to this matter.
<br>
<br>
Best regards,<br>
{{ config('app.name') }}
</x-mail::message>