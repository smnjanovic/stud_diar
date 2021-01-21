<?php
namespace Model;

class FormKeys {
  // účtový formulár
  const SIGN_IN_FORM = "access-account";
  const SIGN_UP_FORM = "create-account";
  const SIGN_OUT_FORM = "log-out";
  const ACCOUNT_REMOVAL = "remove-account";
  const FORGOT_PASSWORD = "forgot-password";
  const MODIFY_ACCOUNT = "user-edit";
  const CHANGE_PASSWORD = "change-password";


  // užívateľské údaje
  const USER_ID = "ID";
  const NICK = "nick";
  const EMAIL = "email";
  const PASS = "pass";
  const PASS2 = "pass2";
  const OLD_PASS = "old_pass";
  const NEW_PASS1 = "new_pass1";
  const NEW_PASS2 = "new_pass2";

  const STORAGE_SUBJECTS = "subjects";
  const STORAGE_NOTES = "notes";
  const STORAGE_SCHEDULE = "schedule";

  //predmet
  const SUB_ID = "id";
  const SUB_ABB = "abb";
  const SUB_NAME = "name";
}
