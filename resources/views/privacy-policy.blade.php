<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Learn how information is handled when you use {{ config('app.name', 'the CRM') }}.">
    <title>Privacy Policy | {{ config('app.name', 'CRM') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
    <style>
        body { background: #f5f7f9; }
        .policy-card { max-width: 900px; }
        .policy-card h2 { margin-top: 2rem; font-size: 1.2rem; }
        .policy-card p, .policy-card li { color: #4b5563; line-height: 1.7; }
    </style>
</head>
<body>
    <main class="container py-5">
        <article class="policy-card mx-auto bg-white border rounded-4 shadow-sm p-4 p-md-5">
            <a href="{{ url('/') }}" class="small text-decoration-none">&larr; Back to application</a>
            <h1 class="mt-4 mb-2">Privacy Policy</h1>
            <p class="text-muted mb-4">{{ config('app.name', 'This CRM') }}</p>

            <p>
                This policy explains how information is handled when you use
                {{ config('app.name', 'this CRM application') }}. The organization that
                provides your account operates the CRM and determines how its records
                are used. Contact that organization for questions about its privacy
                practices or your information.
            </p>

            <h2>Information in the CRM</h2>
            <p>The CRM may contain information needed to manage business operations, including:</p>
            <ul>
                <li>Account details such as your name, work contact information, role, and sign-in activity.</li>
                <li>Customer, lead, employee, vendor, and other business contact details entered by authorized users.</li>
                <li>Operational records such as projects, visits, attendance, quotations, invoices, payments, service requests, and work orders.</li>
                <li>Documents or other files that users choose to upload, and activity records such as actions and timestamps.</li>
            </ul>

            <h2>How information is used</h2>
            <p>
                Information is used to provide and administer the CRM, support customer
                and business operations, manage user access, maintain records, and
                protect or troubleshoot the service. The organization using the CRM
                decides which records to enter and who may access them.
            </p>

            <h2>Access and sharing</h2>
            <p>
                Information is available to users who have been granted access by the
                organization operating the CRM. It may also be processed by service
                providers engaged to host or support the application, where applicable,
                or disclosed when required by law or to protect the rights and security
                of the organization and its users.
            </p>

            <h2>Retention and your choices</h2>
            <p>
                The organization operating the CRM controls how long records are kept
                and when they are corrected or removed, subject to its legal and
                operational obligations. To request access to, correction of, or
                deletion of information, contact that organization’s CRM administrator
                or privacy contact.
            </p>

            <h2>Security</h2>
            <p>
                The application provides account access controls and may record
                administrative activity. The organization operating the CRM is
                responsible for configuring access appropriately and protecting its
                accounts, devices, and records. No method of storage or transmission
                can be guaranteed to be completely secure.
            </p>

            <h2>Updates to this policy</h2>
            <p>
                This policy may be updated as the application or its use changes.
                Material updates should be communicated by the organization operating
                the CRM. Please contact its administrator if you have questions.
            </p>
        </article>
    </main>
</body>
</html>
