<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EasySchool User Manual</title>
    <style>
        @page { margin: 22mm 16mm 20mm 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; line-height: 1.45; }
        h1 { font-size: 20px; color: #0f766e; margin: 0 0 10px; page-break-before: always; border-bottom: 3px solid #25A194; padding-bottom: 6px; }
        h1.first { page-break-before: auto; }
        h2 { font-size: 14px; color: #115e59; margin: 16px 0 8px; }
        h3 { font-size: 12px; color: #334155; margin: 12px 0 6px; }
        p { margin: 0 0 8px; }
        ul, ol { margin: 0 0 10px; padding-left: 18px; }
        li { margin-bottom: 3px; }
        .cover { text-align: center; page-break-after: always; }
        .cover img { width: 100%; border-radius: 8px; }
        .cover h1 { border: 0; font-size: 28px; page-break-before: auto; margin-top: 16px; }
        .muted { color: #64748b; }
        .fig { margin: 8px 0 14px; page-break-inside: avoid; }
        .fig img { width: 100%; border: 1px solid #cbd5e1; }
        .caption { font-size: 9px; color: #475569; text-align: center; margin-top: 4px; font-style: italic; }
        table { width: 100%; border-collapse: collapse; margin: 0 0 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 7px; text-align: left; vertical-align: top; font-size: 10px; }
        th { background: #e6f4f2; color: #0f766e; }
        .mod { background: #f0fdfa; font-weight: bold; color: #0f766e; }
        .tip { background: #ecfeff; border: 1px solid #99f6e4; padding: 8px 10px; margin: 8px 0 12px; }
        .warn { background: #fff7ed; border: 1px solid #fed7aa; padding: 8px 10px; margin: 8px 0 12px; }
        .num { display: inline-block; width: 16px; height: 16px; background: #25A194; color: #fff; text-align: center; font-size: 10px; font-weight: bold; }
        .footer { font-size: 9px; color: #64748b; }
        .toc td { font-size: 11px; }
        a { color: #0f766e; text-decoration: none; }
    </style>
</head>
<body>
@php
    $pic = function (string $file): string {
        $name = str_ends_with($file, '.png') ? preg_replace('/\.png$/', '.jpg', $file) : $file;

        return str_replace('\\', '/', public_path('docs/manual/'.$name));
    };
@endphp

<div class="cover">
    <img src="{{ $pic('manual-cover.png') }}" alt="EasySchool User Manual cover">
    <h1 class="first">EasySchool End-User Manual</h1>
    <p style="font-size:16px;color:#0f766e;font-weight:bold;">{{ $schoolName }}</p>
    <p class="muted">School Management Information System &middot; How to use every module</p>
    <p class="muted">Generated {{ $generatedAt }}</p>
</div>

<h1 class="first">1. Welcome</h1>
<p>EasySchool is the school management information system used to run day-to-day academic, finance, HR, shop, and communication work. What you see in the left menu depends on the <strong>user category</strong> (role) assigned to your login. An administrator can grant extra pages under <em>User Management &rarr; User Categories</em>.</p>
<p>This manual lists <strong>every module in the system</strong>, then walks through how to use each one. The pictures are labelled figures that match the live screens.</p>

<div class="tip"><strong>Before you start.</strong> Ask an administrator for your email (or staff ID) and password. Set the school name, logo, current academic year, and term under Settings so bills, reports, and report cards print with the correct letterhead.</div>

<h2>How the screen is laid out</h2>
<div class="fig">
    <img src="{{ $pic('manual-sidebar.png') }}" alt="Sidebar menu">
    <div class="caption">Figure 1. Left sidebar &mdash; open a module here. The items you see depend on your access.</div>
</div>
<ol>
    <li><strong>Sidebar (left)</strong> &mdash; main modules. Click a heading to open its pages.</li>
    <li><strong>Top bar</strong> &mdash; your name, photo, and logout.</li>
    <li><strong>Page header</strong> &mdash; title, breadcrumbs, and action buttons (Add, Print, PDF, Excel).</li>
    <li><strong>Work area</strong> &mdash; forms, tables, KPI cards, and filters.</li>
</ol>

<h1>2. Complete module list</h1>
<p>These are all parent modules and their pages in EasySchool.</p>
<table>
    <thead>
        <tr><th>Module</th><th>Pages / functions</th><th>Typical users</th></tr>
    </thead>
    <tbody>
        <tr><td class="mod">Dashboard</td><td>Role-aware home, KPIs, shortcuts, charts</td><td>Everyone</td></tr>
        <tr><td class="mod">Students</td><td>Register New Student; Student List; view / edit / print profile</td><td>Admin, office</td></tr>
        <tr><td class="mod">Student Remarks</td><td>Add Remarks; List Remarks. Attitude, conduct, interest, class teacher's remarks, and headteacher's signature</td><td>Admin, class teacher</td></tr>
        <tr><td class="mod">Class Setup</td><td>Classes; Class Categories; Student Class Assignment; Student Promotion</td><td>Admin</td></tr>
        <tr><td class="mod">Course Setup</td><td>Add Course (and sub-courses); Course Registration</td><td>Admin</td></tr>
        <tr><td class="mod">Teacher Management</td><td>Teacher Directory; Class Teachers; Course Teachers; Grading Scheme</td><td>Admin</td></tr>
        <tr><td class="mod">Teacher Portal</td><td>Dashboard; Attendance; Assessments; Assessment Records; Gradebook; report cards</td><td>Teachers</td></tr>
        <tr><td class="mod">Leave</td><td>Request leave</td><td>Everyone</td></tr>
        <tr><td class="mod">HR</td><td>Dashboard; Add Employee; Employees; Departments; Positions; Leave; Attendance; Salary Structures; Payroll; Payslips; Appraisals; Statutory Settings</td><td>HR, admin</td></tr>
        <tr><td class="mod">Bill Management</td><td>Billing Items; Category Bill Setup; Student Bills; Edit Student Bills; Print Bills; Record Payment (Cash / MoMo / Paystack)</td><td>Bursar, cashier</td></tr>
        <tr><td class="mod">POS</td><td>New Sale; Sales History; Products; Categories; Stock Adjustments</td><td>Shop / cashier</td></tr>
        <tr><td class="mod">Expenses</td><td>Record Expenses; Expense Categories</td><td>Bursar, admin</td></tr>
        <tr><td class="mod">Timetable</td><td>Class Timetables; Period times; generate week; print</td><td>Admin</td></tr>
        <tr><td class="mod">Send SMS</td><td>Compose SMS to teachers, staff, a class, whole school, or individuals</td><td>Admin, office</td></tr>
        <tr><td class="mod">Reports</td><td>Students; Enrolment; Staff; Leave; Staff attendance; Payroll; Fee collection; Outstanding bills; POS sales; Expenses; Class attendance; SMS log &mdash; each with Print, PDF, Excel</td><td>Admin, management</td></tr>
        <tr><td class="mod">Parent Portal</td><td>Parent sign-in; dashboard; child overview; academics; fees &amp; bills (Paystack); payment history &amp; receipts; report card; messages to school</td><td>Parents / guardians</td></tr>
        <tr><td class="mod">Parent Messages</td><td>Inbox of messages sent by parents (staff view under Send SMS)</td><td>Admin, office</td></tr>
        <tr><td class="mod">Dormitory</td><td>Houses; dormitories; beds; assign / unassign students</td><td>Admin, boarding</td></tr>
        <tr><td class="mod">Settings</td><td>School Firm Setup; Academic Years; Academic Terms; Academic Session; Assessment Types; Promotion Settings</td><td>Admin</td></tr>
        <tr><td class="mod">User Management</td><td>User Categories (roles and menu access); System Access (staff logins)</td><td>Admin</td></tr>
        <tr><td class="mod">Profile</td><td>Your photo and password</td><td>Everyone</td></tr>
    </tbody>
</table>

<h1>3. Sign in and sign out</h1>
<div class="fig">
    <img src="{{ $pic('manual-login.png') }}" alt="Login screen">
    <div class="caption">Figure 2. Sign-in page. Use the email or staff ID your school gave you.</div>
</div>
<h2>How to sign in</h2>
<ol>
    <li>Open the school EasySchool address in your browser (for example <em>http://127.0.0.1:8001</em> on this computer).</li>
    <li>In <strong>Staff / Student ID / Email</strong>, type your email address.</li>
    <li>Type your password. Use the eye icon if you need to check what you typed.</li>
    <li>Click <strong>Sign in</strong>.</li>
    <li>You land on the Dashboard. Only menus your category is allowed to see appear in the sidebar.</li>
</ol>
<div class="warn"><strong>Locked account.</strong> Repeated wrong passwords can lock the account. Ask an administrator to unlock you or reset the password from the staff record.</div>
<h2>How to sign out</h2>
<ol>
    <li>Open your name or photo in the top-right corner.</li>
    <li>Choose logout. Always sign out on a shared computer.</li>
</ol>

<h1>4. Dashboard</h1>
<div class="fig">
    <img src="{{ $pic('manual-dashboard.png') }}" alt="Dashboard">
    <div class="caption">Figure 3. Dashboard with KPI cards, shortcuts, and charts filtered to your privileges.</div>
</div>
<ol>
    <li>After login you see a greeting, your role badge, and the current academic year / term (if set).</li>
    <li>Coloured cards show counts you are allowed to see (students, staff, classes, fees, and so on).</li>
    <li>Shortcut tiles open a module in one click (for example Reports or POS).</li>
    <li>Charts (if shown) summarise attendance, fees, or similar data for your role.</li>
    <li>If you see “No modules are assigned”, ask an administrator to grant menu access on your user category.</li>
</ol>

<h1>5. Students</h1>
<p>Use this module to admit learners, keep their records, and print profiles.</p>

<h2>5.1 Register a new student</h2>
<p><strong>Menu:</strong> Students &rarr; Register New Student</p>
<div class="fig">
    <img src="{{ $pic('manual-register.png') }}" alt="Student registration wizard">
    <div class="caption">Figure 4. Four-step registration wizard: Personal, Parent, Medical, Documents.</div>
</div>
<ol>
    <li>Open <strong>Register New Student</strong>. A student ID is prepared for you.</li>
    <li><strong>Step 1 — Personal Info.</strong> Enter name, gender, date of birth, class (optional at this stage), photo, and contact fields. Required fields must be filled before you continue.</li>
    <li>Click <strong>Save &amp; continue</strong> (the draft is stored so you do not lose work).</li>
    <li><strong>Step 2 — Parent &amp; Guardian.</strong> Enter father, mother, and guardian names and phones. Guardian phone is important for SMS and bills.</li>
    <li><strong>Step 3 — Medical &amp; Other.</strong> Add medical notes, addresses, and extra remarks.</li>
    <li><strong>Step 4 — Documents.</strong> Attach birth certificate, previous school reports, or other files.</li>
    <li>Click <strong>Register Student</strong>. The learner appears on the Student List with status Active.</li>
</ol>
<div class="tip"><strong>Tip.</strong> You can place a student in a class later under Class Setup &rarr; Student Class Assignment if you skip class on step 1.</div>

<h2>5.2 Student list, view, edit, print</h2>
<p><strong>Menu:</strong> Students &rarr; Student List</p>
<div class="fig">
    <img src="{{ $pic('manual-students.png') }}" alt="Student list">
    <div class="caption">Figure 5. Student list with search, class filter, avatars, and status pills.</div>
</div>
<ol>
    <li>Filter by class or status, or type a name / ID in search.</li>
    <li>Open a row to <strong>view</strong> the full profile (personal, parents, medical, documents, bills).</li>
    <li>Use <strong>Edit</strong> to change details with the same four-step wizard.</li>
    <li>Use <strong>Print</strong> for a paper profile. A signed public profile link can also be used where enabled.</li>
    <li>Inactive students stay in history; they no longer appear as Active on enrolment reports if you filter to Active.</li>
</ol>

<h1>6. Class Setup</h1>
<h2>6.1 Class categories</h2>
<p><strong>Menu:</strong> Class Setup &rarr; Class Categories</p>
<ol>
    <li>Add groups such as Creche, KG, Primary, JHS. These drive <strong>Category Bill Setup</strong> (the same fee pack for every class in the category).</li>
    <li>Give each category a clear name and keep it Active.</li>
</ol>

<h2>6.2 Classes</h2>
<p><strong>Menu:</strong> Class Setup &rarr; Classes</p>
<ol>
    <li>Click add, enter the class name (for example <em>P3 A</em>), pick a category, and save.</li>
    <li>Keep unused classes Inactive instead of deleting them if they already have history.</li>
</ol>

<h2>6.3 Assign students to classes</h2>
<p><strong>Menu:</strong> Class Setup &rarr; Student Class Assignment</p>
<ol>
    <li>Search a student, choose the destination class, and assign.</li>
    <li>Use bulk assignment to move a list of unassigned or selected learners into one class.</li>
    <li>Preview before you confirm a bulk move.</li>
</ol>

<h2>6.4 Student promotion</h2>
<p><strong>Menu:</strong> Class Setup &rarr; Student Promotion</p>
<ol>
    <li>First set pass marks under Settings &rarr; Promotion Settings.</li>
    <li>Open a class. EasySchool shows who meets the promotion rule.</li>
    <li>Select learners and promote them to the next class for the new year.</li>
</ol>
<div class="warn">Promotion uses assessment scores and the grading / promotion settings. Complete gradebook entry before you promote.</div>

<h1>7. Course Setup</h1>
<h2>7.1 Add courses</h2>
<p><strong>Menu:</strong> Course Setup &rarr; Add Course (or the courses page in that menu)</p>
<ol>
    <li>Create a course (for example English Language). You can add sub-courses where the school splits a subject.</li>
    <li>Keep names aligned with the Ghana Education Service subjects your school teaches.</li>
</ol>

<h2>7.2 Course registration</h2>
<p><strong>Menu:</strong> Course Setup &rarr; Course Registration</p>
<ol>
    <li>Pick the class (and year/term if asked).</li>
    <li>Tick the courses that class should take this term.</li>
    <li>Unregister a course if it was added by mistake. Teachers only see classes/courses they are assigned to.</li>
</ol>

<h1>8. Teacher Management</h1>
<p>This is the office side of teaching. Daily marking lives in the Teacher Portal.</p>

<h2>8.1 Teacher directory</h2>
<p>Lists staff flagged as teachers. Open a person to jump to their employee record.</p>

<h2>8.2 Class teachers (homeroom)</h2>
<p><strong>Menu:</strong> Teacher Management &rarr; Class Teachers</p>
<ol>
    <li>Pick a class and assign the homeroom teacher.</li>
    <li>That teacher gets the class on Teacher Portal &rarr; Attendance and can take the daily register.</li>
    <li>Unassign if the teacher leaves the class.</li>
</ol>

<h2>8.3 Course teachers</h2>
<p><strong>Menu:</strong> Teacher Management &rarr; Course Teachers</p>
<ol>
    <li>Assign a teacher to a course in a class (for example Maths in JHS 2).</li>
    <li>That teacher can create assessments and enter scores for that course.</li>
</ol>

<h2>8.4 Grading scheme</h2>
<p><strong>Menu:</strong> Teacher Management &rarr; Grading Scheme</p>
<ol>
    <li>Define grade letters and mark ranges (for example A = 80–100).</li>
    <li>Report cards and promotion use this scheme. Edit carefully once a term has scores.</li>
</ol>

<h1>9. Teacher Portal</h1>
<p>Teachers use this menu for daily work. Subject teachers see their courses; class teachers also see homeroom attendance.</p>

<h2>9.1 Teacher dashboard</h2>
<ol>
    <li>Open <strong>Teacher Portal &rarr; Dashboard</strong>.</li>
    <li>You see your homeroom classes and course assignments as cards.</li>
    <li>Use Roster, Attendance, Assessments, or Gradebook on each card.</li>
</ol>

<h2>9.2 Take class attendance</h2>
<p><strong>Menu:</strong> Teacher Portal &rarr; Attendance</p>
<div class="fig">
    <img src="{{ $pic('manual-attendance.png') }}" alt="Attendance mark sheet">
    <div class="caption">Figure 6. Daily mark sheet. Homeroom teachers mark Present, Absent, Late, or Excused.</div>
</div>
<ol>
    <li>Open Attendance. Only your homeroom classes appear as cards.</li>
    <li>Click <strong>Open Mark Sheet</strong>.</li>
    <li>Choose the date (defaults to today).</li>
    <li>For each student tap <strong>Present</strong>, <strong>Absent</strong>, <strong>Late</strong>, or <strong>Excused</strong>. Add a short note if needed (for example sick).</li>
    <li>Save the register. Office staff can later print the Class attendance report.</li>
</ol>
<div class="tip">Subject teachers do not mark the daily register unless they are also the class teacher.</div>

<h2>9.3 Assessments and scores</h2>
<p><strong>Menu:</strong> Teacher Portal &rarr; Assessments</p>
<ol>
    <li>Open a class or a course workspace.</li>
    <li>Create an assessment: name, type (homework, class test, exam — from Settings &rarr; Assessment Types), date, and total marks.</li>
    <li>Open <strong>Scores</strong> and enter each student’s mark. Save.</li>
    <li>Assessment Records lists past assessments. You can delete an assessment only if your school allows it and scores are not locked.</li>
</ol>

<h2>9.4 Gradebook and report cards</h2>
<p><strong>Menu:</strong> Teacher Portal &rarr; Gradebook</p>
<ol>
    <li>Open the class gradebook for the current year and term.</li>
    <li>Review combined scores and grades from the grading scheme.</li>
    <li>Print one student’s report card, or print the whole class set (school letterhead comes from Settings).</li>
</ol>

<h2>9.5 Apply for leave</h2>
<p><strong>Menu:</strong> Leave &rarr; Request leave</p>
<ol>
    <li>Choose a leave type, start date, and end date. Add a short reason if you want.</li>
    <li>Submit. The request stays pending until an administrator or HR approves or rejects it on HR &rarr; Leave.</li>
    <li>Your remaining days and past requests are shown on the same page.</li>
</ol>

<h1>10. HR</h1>
<div class="fig">
    <img src="{{ $pic('manual-hr.png') }}" alt="HR payroll">
    <div class="caption">Figure 7. HR payroll — generate a month, review payslips, then approve and mark paid.</div>
</div>

<h2>10.1 Add an employee</h2>
<p><strong>Menu:</strong> HR &rarr; Add Employee</p>
<ol>
    <li>Complete personal details, job, department, and position.</li>
    <li>If the person should sign in, open User Management &rarr; System Access, choose them, and set a password and user category.</li>
    <li>Tick teacher-related flags if they will appear in Teacher Directory and assignments.</li>
    <li>Save. The person appears under Employees. You can attach documents from the profile.</li>
</ol>

<h2>10.2 Employees list</h2>
<ol>
    <li>Search, filter by status or department, and open a record to view or edit.</li>
    <li>Print or view the staff ID card from the profile where available.</li>
</ol>

<h2>10.3 Departments and positions</h2>
<ol>
    <li>Create departments (Academic, Accounts, Administration).</li>
    <li>Create positions (Head Teacher, Teacher, Bursar) and link them to departments as needed.</li>
</ol>

<h2>10.4 Leave</h2>
<ol>
    <li>Define leave types (annual, sick, maternity) if they are not already there.</li>
    <li>Teachers submit their own requests from Leave &rarr; Request leave.</li>
    <li>Approve or reject on HR &rarr; Leave. Approved leave shows on the HR attendance calendar.</li>
</ol>

<h2>10.5 Staff attendance</h2>
<ol>
    <li>Open HR &rarr; Attendance.</li>
    <li>Mark present / absent (and check-in / check-out if you use times) for the day.</li>
</ol>

<h2>10.6 Salary structures</h2>
<ol>
    <li>Create pay grades, earning types (allowances), and deduction types.</li>
    <li>Assign a grade or basic salary on the employee’s HR employment section.</li>
</ol>

<h2>10.7 Payroll and payslips</h2>
<ol>
    <li>Open HR &rarr; Payroll. Choose year and month.</li>
    <li>Generate the draft. EasySchool calculates gross, SSNIT, PAYE, and net from statutory settings and the employee’s structure.</li>
    <li>Open the run, review each payslip, then <strong>Approve</strong> (this locks totals).</li>
    <li>When money has been paid, mark the run <strong>Paid</strong>.</li>
    <li>HR &rarr; Payslips lists slips. Print an individual payslip for the staff file.</li>
</ol>

<h2>10.8 Appraisals and statutory settings</h2>
<ol>
    <li>Record appraisals against an employee when your school uses performance reviews.</li>
    <li>Statutory Settings hold SSNIT and PAYE parameters used by payroll. Change them only when tax rules change, and preferably before generating a new month.</li>
</ol>

<h1>11. Bill Management</h1>
<div class="fig">
    <img src="{{ $pic('manual-billing.png') }}" alt="Fee cashier">
    <div class="caption">Figure 8. Cashier screen — student balance, bill lines, and Record Payment.</div>
</div>

<h2>11.1 Billing items</h2>
<ol>
    <li>Create items the school charges: Tuition, PTA, Feeding, Uniform, Exam levy.</li>
    <li>Keep names stable; reports group by these items.</li>
</ol>

<h2>11.2 Category bill setup</h2>
<ol>
    <li>Pick a class category (for example Primary) and academic year / term.</li>
    <li>Tick items and amounts that apply to every class in that category.</li>
    <li>Save. New or assigned students in those classes receive the matching bills.</li>
</ol>

<h2>11.3 Student bills and editing</h2>
<ol>
    <li><strong>Student Bills</strong> is the ledger: who owes what. Filter by class or year. Print one statement or the class ledger.</li>
    <li><strong>Edit Student Bills</strong> — search a student to adjust a line (for example a scholarship or extra charge). Use this sparingly and keep a note of why.</li>
</ol>

<h2>11.4 Record a payment (cashier)</h2>
<ol>
    <li>From Student Bills, open the student and go to record payment (cashier).</li>
    <li>Confirm the outstanding balance on the profile header.</li>
    <li>Enter amount, method (Cash, Mobile Money, or Paystack), and optional reference.</li>
    <li>Submit. Print or give the receipt. Paystack payments wait until the parent completes checkout; the system verifies the payment.</li>
</ol>

<h2>11.5 Print bills</h2>
<ol>
    <li>Open Print Bills. Choose a student or a whole class.</li>
    <li>Print statements for parents. Letterhead uses School Firm Setup (name, logo, address).</li>
</ol>

<h1>12. POS (school shop)</h1>
<div class="fig">
    <img src="{{ $pic('manual-pos.png') }}" alt="POS terminal">
    <div class="caption">Figure 9. POS terminal — pick a category, tap products, then charge the cart.</div>
</div>

<h2>12.1 Categories and products</h2>
<ol>
    <li>POS &rarr; Categories: add groups such as Uniforms, Books, Stationery.</li>
    <li>POS &rarr; Products: name, category, selling price (GHS), photo, and stock-related fields.</li>
</ol>

<h2>12.2 Stock adjustments</h2>
<ol>
    <li>Open Stock Adjustments when goods arrive or are damaged.</li>
    <li>Choose the product, quantity in or out, and a reason. Save so the shop stock stays correct.</li>
</ol>

<h2>12.3 Make a sale</h2>
<ol>
    <li>Open POS &rarr; New Sale.</li>
    <li>Optionally search and attach a student as the customer.</li>
    <li>Tap a category box, then tap products to add them to the cart. Adjust quantities if needed.</li>
    <li>Choose payment: Cash, Mobile Money, or Paystack.</li>
    <li>Complete the sale. Print the receipt from the success screen or later from Sales History.</li>
</ol>

<h2>12.4 Sales history</h2>
<p>Filter by date, reopen a sale, and reprint the receipt.</p>

<h1>13. Expenses</h1>
<p><strong>Menu:</strong> Expenses &rarr; Categories, then Record Expenses</p>
<ol>
    <li>Create categories (Utilities, Transport, Maintenance, Fuel).</li>
    <li>On Record Expenses, click add. Enter date, category, payee, method, amount in GHS, and reference (cheque or MoMo ID).</li>
    <li>Edit or delete a row if it was entered in error (only if your role allows delete).</li>
    <li>Use the KPI cards and category bars on the ledger to see spend in the selected period.</li>
</ol>

<h1>14. Timetable</h1>
<div class="fig">
    <img src="{{ $pic('manual-timetable.png') }}" alt="Class timetable">
    <div class="caption">Figure 10. Weekly class timetable with coloured subject blocks.</div>
</div>

<h2>14.1 Period times</h2>
<ol>
    <li>Open Timetable &rarr; Period times.</li>
    <li>Pick a class and weekday. Add periods, breaks, and assembly with start and end times.</li>
    <li>This daily template is reused when you generate or edit the week.</li>
</ol>

<h2>14.2 Class timetables</h2>
<ol>
    <li>Open Class Timetables and choose a class (and year/term).</li>
    <li>Generate the week from the period template, then place subjects and teachers in each slot.</li>
    <li>Print the timetable for the staffroom or the class notice board.</li>
</ol>

<h1>15. Send SMS</h1>
<div class="fig">
    <img src="{{ $pic('manual-sms.png') }}" alt="Send SMS">
    <div class="caption">Figure 11. Compose SMS — choose an audience, write the message, then send.</div>
</div>
<ol>
    <li>Open Send SMS &rarr; Compose SMS.</li>
    <li>Choose the audience:
        <ul>
            <li><strong>Teachers</strong> — active teaching staff</li>
            <li><strong>All staff</strong> — every active employee</li>
            <li><strong>A class</strong> — parents / guardians of that class</li>
            <li><strong>Entire school</strong> — all student guardians</li>
            <li><strong>Individual</strong> — type names; pick people as chips (not a long checkbox list)</li>
        </ul>
    </li>
    <li>If you chose a class, select the class. The recipient count updates.</li>
    <li>Type the message. Watch the character count.</li>
    <li>Click send. Recent campaigns appear on the same page with sent counts and status.</li>
</ol>
<div class="warn">SMS uses the school SMS gateway configured on the server. People without a phone number are skipped. Check the SMS log report if a campaign looks incomplete.</div>

<h1>16. Reports</h1>
<p>Every report has the same pattern: filter, preview, then <strong>Print</strong> (indigo), <strong>PDF</strong> (rose), or <strong>Excel</strong> (green). Filters you apply on screen are included in the export.</p>
<div class="fig">
    <img src="{{ $pic('manual-reports.png') }}" alt="Enrolment report">
    <div class="caption">Figure 12. Enrolment report — KPI cards, gender mix bar, and Print / PDF / Excel.</div>
</div>
<h2>How to run any report</h2>
<ol>
    <li>Open Reports and pick the report from the sidebar.</li>
    <li>Set filters (class, dates, status). Click <strong>Apply</strong>.</li>
    <li>Read the coloured summary cards, then scroll the table.</li>
    <li>Click <strong>Print</strong> for a letterhead print view, <strong>PDF</strong> to download, or <strong>Excel</strong> for a spreadsheet.</li>
</ol>
<table>
    <thead><tr><th>Report</th><th>What it shows</th><th>Typical use</th></tr></thead>
    <tbody>
        <tr><td>Student list</td><td>Learners, class, gender, status, phone</td><td>Roll / contacts</td></tr>
        <tr><td>Enrolment</td><td>Headcount by class and gender, mix bar</td><td>Inspectors, planning</td></tr>
        <tr><td>Staff list</td><td>Employees, department, position, status</td><td>HR files</td></tr>
        <tr><td>Leave</td><td>Leave applications and days</td><td>HR reviews</td></tr>
        <tr><td>Staff attendance</td><td>Daily staff marks, check-in / out</td><td>Punctuality</td></tr>
        <tr><td>Payroll</td><td>Payslip gross, SSNIT, PAYE, net</td><td>Finance</td></tr>
        <tr><td>Fee collection</td><td>Payments received with receipts</td><td>Daily takings</td></tr>
        <tr><td>Outstanding bills</td><td>Balances still due</td><td>Debt follow-up</td></tr>
        <tr><td>POS sales</td><td>Shop sales by receipt and cashier</td><td>Shop audit</td></tr>
        <tr><td>Expenses</td><td>Outgoing spend by category</td><td>Budget</td></tr>
        <tr><td>Class attendance</td><td>Present / absent / late / excused register</td><td>Parents, GES</td></tr>
        <tr><td>SMS log</td><td>Campaigns sent and counts</td><td>Communications</td></tr>
    </tbody>
</table>

<h1>17. Dormitory</h1>
<p><strong>Menu:</strong> Dormitory &rarr; Dormitory Setup</p>
<ol>
    <li>Add <strong>houses</strong> (for example Red House, Blue House).</li>
    <li>Add <strong>dormitories</strong> under a house (rooms or halls) with bed capacity.</li>
    <li>Assign a boarding student to a house, dormitory, and free bed. Unassign when they leave or change room.</li>
    <li>The page shows available beds so you do not overbook a dorm.</li>
</ol>

<h1>18. Settings</h1>
<h2>18.1 School Firm Setup</h2>
<p><strong>Menu:</strong> Settings &rarr; School Firm Setup</p>
<ol>
    <li>Upload the school logo.</li>
    <li>Enter official name, motto, address, phone, email, and website.</li>
    <li>Save. This letterhead appears on bills, receipts, report cards, timetables, and report PDFs.</li>
</ol>

<h2>18.2 Academic years and terms</h2>
<ol>
    <li>Create the academic year (for example 2025/2026) and mark it Active.</li>
    <li>Create terms (Term 1, Term 2, Term 3) with start and end dates, linked to a year.</li>
</ol>

<h2>18.3 Academic session (defaults)</h2>
<ol>
    <li>Open Academic Session.</li>
    <li>Set the <strong>default year</strong> and <strong>default term</strong> the whole school is working in.</li>
    <li>Dashboards, gradebook, bills, and timetables follow these defaults unless a page lets you pick another period.</li>
</ol>

<h2>18.4 Assessment types</h2>
<ol>
    <li>Add types teachers can pick: Homework, Class test, Exam, Project.</li>
    <li>Teachers cannot create an assessment until at least one type exists.</li>
</ol>

<h2>18.5 Promotion settings</h2>
<ol>
    <li>Set the pass mark or rule used by Student Promotion.</li>
    <li>Save before the promotion exercise at the end of the year.</li>
</ol>

<h1>19. User Management</h1>
<p><strong>Menu:</strong> User Management &rarr; User Categories</p>
<ol>
    <li>A <strong>user category</strong> is a role (Administrator, Bursar, Teacher, Cashier).</li>
    <li>Add a category and tick every menu page that role should see.</li>
    <li>Edit a category to grant a new module (for example Reports or POS) without creating a new login.</li>
    <li>Under System Access, choose the staff member, turn on login, and give them a category and password so they can sign in.</li>
</ol>
<div class="tip">If someone says a page is missing, first check their category ticks, then confirm the employee is Active and has a login.</div>

<h1>20. Parent Portal</h1>
<p>The <strong>Parent Portal</strong> is a separate website for guardians. It uses a different sign-in page from staff (<em>/parent/login</em>). Parents see only their own children &mdash; linked by the <strong>guardian phone number</strong> stored on each student record.</p>

<div class="tip"><strong>Account setup.</strong> When a student is registered or updated, EasySchool can create a parent account from the guardian phone. Administrators can backfill all active students with <em>php artisan parent:sync-accounts</em>. The default password is set in school configuration (commonly <em>Parent123</em> until the parent changes it).</div>

<h2>20.1 How parents sign in</h2>
<div class="fig">
    <img src="{{ $pic('manual-parent-login.png') }}" alt="Parent portal sign in">
    <div class="caption">Figure 13. Parent sign-in &mdash; guardian phone and password. Staff use the main school login instead.</div>
</div>
<ol>
    <li>Open <strong>/parent/login</strong> (for example <em>http://127.0.0.1:8000/parent/login</em>).</li>
    <li>Enter the <strong>guardian phone</strong> number exactly as stored on the student record.</li>
    <li>Enter the password the school provided.</li>
    <li>Click <strong>Sign in</strong>. Parents with more than one child see all linked learners on the dashboard.</li>
</ol>
<div class="warn"><strong>Wrong phone?</strong> If no children appear, confirm the guardian phone on the student profile matches the number the parent is using. Update the student record and run <em>parent:sync-accounts</em> if needed.</div>

<h2>20.2 Parent dashboard and child overview</h2>
<div class="fig">
    <img src="{{ $pic('manual-parent.png') }}" alt="Parent portal dashboard">
    <div class="caption">Figure 14. Parent dashboard &mdash; linked children, outstanding fees, quick pay links, and message to school.</div>
</div>
<ol>
    <li>After login the parent sees a welcome banner, school name, and a card for each linked child.</li>
    <li>Each card shows class, student ID, and <strong>outstanding fees</strong>. A green tick means fees are clear; red means payment is due.</li>
    <li>Click <strong>Overview</strong> on a child to open their workspace with colourful quick-access tiles.</li>
    <li>Quick tiles include <strong>Academics</strong>, <strong>Fees &amp; bills</strong>, <strong>Payments</strong>, <strong>Report card</strong>, and <strong>Messages</strong>.</li>
    <li>Use the form at the bottom of the dashboard to send a quick message to the school office.</li>
</ol>

<h2>20.3 Academics and report card</h2>
<ol>
    <li><strong>Academics</strong> &mdash; pick academic year and term to view subject grades, class position, term average, and attendance summary.</li>
    <li><strong>Report card</strong> &mdash; same period filters; preview grades and attendance, then click <strong>Print official report card</strong> to open the formal terminal report in a new tab for printing or saving as PDF.</li>
    <li>Parents cannot edit grades; they only view published results.</li>
</ol>

<h2>20.4 Fees, bills, and online payment</h2>
<div class="fig">
    <img src="{{ $pic('manual-parent-bills.png') }}" alt="Parent fees and bills">
    <div class="caption">Figure 15. Parent fees &amp; bills &mdash; outstanding table, statement tab, and Paystack pay panel.</div>
</div>
<ol>
    <li>Open <strong>Fees &amp; bills</strong> for a child. The banner shows total outstanding, credit balance, and net amount to pay.</li>
    <li>Filter by academic year and term if needed.</li>
    <li>On the <strong>Bills to pay</strong> tab, review each fee item, amount due, and progress.</li>
    <li>On the <strong>Statement</strong> tab, see the full bill history with status (Paid, Partial, Pending).</li>
    <li>If Paystack is configured and there is a balance, use the <strong>Pay now</strong> panel: choose full balance or enter an amount, then complete payment on the Paystack screen (Mobile Money or card).</li>
    <li>After a successful payment, open the receipt from the confirmation screen or from <strong>Payments</strong>.</li>
</ol>

<h2>20.5 Payment history and receipts</h2>
<ol>
    <li><strong>Payments</strong> lists every receipt with date, method, and amount.</li>
    <li>Click <strong>Receipt</strong> to open a printable payment receipt showing how the money was applied to each bill.</li>
    <li>Receipts show cash collected, credit applied, and any overpayment saved as credit.</li>
</ol>

<h2>20.6 Messages and school SMS</h2>
<ol>
    <li><strong>Messages</strong> lets a parent compose a note to the school (general or about a specific child).</li>
    <li>The <strong>Received messages</strong> panel shows SMS alerts and school notices sent to that parent or their child&rsquo;s class.</li>
    <li>School staff reply through the office; parents are notified by SMS when campaigns are sent from Send SMS.</li>
</ol>

<h1>21. Parent Messages (staff)</h1>
<p><strong>Menu:</strong> Send SMS &rarr; Parent Messages</p>
<ol>
    <li>Open the inbox to see messages parents sent from the portal.</li>
    <li>New messages are highlighted. Click <strong>View</strong> to read the full text.</li>
    <li>Add an internal reply note if needed, then click <strong>Mark read / save reply</strong>.</li>
    <li>Use this inbox daily so parent enquiries are not missed.</li>
</ol>

<h1>22. Your profile</h1>
<ol>
    <li>Open Profile from the top menu / your name.</li>
    <li>Update your photo if the page allows it.</li>
    <li>Change password: current password, new password (at least 8 characters), confirm. Save.</li>
</ol>

<h1>23. Suggested daily work</h1>
<table>
    <thead><tr><th>Role</th><th>Typical daily / weekly tasks</th></tr></thead>
    <tbody>
        <tr><td>Class teacher</td><td>Mark attendance; create assessments; enter scores; check gradebook</td></tr>
        <tr><td>Subject teacher</td><td>Assessments and scores for assigned courses</td></tr>
        <tr><td>Cashier / bursar</td><td>Record fee payments; POS sales; review outstanding bills; record expenses</td></tr>
        <tr><td>HR</td><td>Staff attendance; leave approvals; monthly payroll</td></tr>
        <tr><td>Office / admin</td><td>Admit students; SMS to parents; parent message inbox; reports; timetable; school settings</td></tr>
        <tr><td>Head / management</td><td>Dashboard KPIs; enrolment and fee reports; payroll approval</td></tr>
        <tr><td>Parent / guardian</td><td>Check fees on the portal; pay online; view grades and report cards; message the school</td></tr>
    </tbody>
</table>

<h1>24. Troubleshooting</h1>
<table>
    <thead><tr><th>Problem</th><th>What to try</th></tr></thead>
    <tbody>
        <tr><td>Cannot see a menu</td><td>Ask admin to tick that page on your user category</td></tr>
        <tr><td>Wrong password / locked</td><td>Admin resets password on the employee record</td></tr>
        <tr><td>Empty dashboard</td><td>No modules granted yet, or school session (year/term) not set</td></tr>
        <tr><td>Teacher cannot mark attendance</td><td>They must be assigned as class teacher for that class</td></tr>
        <tr><td>Teacher cannot enter scores</td><td>Assign them as course teacher; create assessment types; register the course for the class</td></tr>
        <tr><td>Bills missing for a student</td><td>Student must be in a class whose category has Category Bill Setup for this year/term</td></tr>
        <tr><td>SMS not sending</td><td>Check gateway settings, guardian phone numbers, and the SMS log report</td></tr>
        <tr><td>Print / PDF has no school name</td><td>Fill School Firm Setup (name and logo)</td></tr>
        <tr><td>Payroll amounts look wrong</td><td>Check pay grade on the employee and Statutory Settings before regenerating a draft month</td></tr>
        <tr><td>POS stock wrong</td><td>Post a stock adjustment; do not edit history sales</td></tr>
        <tr><td>Parent cannot sign in</td><td>Confirm guardian phone on the student; run <em>parent:sync-accounts</em>; reset parent password if needed</td></tr>
        <tr><td>Parent sees no children</td><td>Guardian phone must match an active student; check spelling and country format</td></tr>
        <tr><td>Parent Pay now fails</td><td>Ensure Paystack keys are set; bills must exist; contact bursar if online pay is disabled</td></tr>
    </tbody>
</table>

<div class="tip" style="margin-top:24px;">
    <strong>Need this file again?</strong> Administrators can regenerate it with the command
    <em>php artisan docs:user-manual</em>.
    The PDF is saved as <em>public/docs/EasySchool-User-Manual.pdf</em>.
</div>

<p class="footer" style="margin-top:28px;">EasySchool User Manual &middot; {{ $schoolName }} &middot; {{ $generatedAt }} &middot; For school staff and parents. Staff menu access is controlled by user categories; parents see only their linked children.</p>
</body>
</html>
