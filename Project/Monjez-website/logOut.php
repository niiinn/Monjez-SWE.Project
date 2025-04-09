<?php
session_start();
session_unset();     // تفرغ كل متغيرات الجلسة
session_destroy();   // تدمر الجلسة بالكامل

header("Location: logIn.php"); // ترجع المستخدم لصفحة تسجيل الدخول
exit();
