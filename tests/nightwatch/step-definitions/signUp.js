const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const signUp = client.page.SignUpPage();

Given(/^Navigate to SignUp Screen$/, () => {
    return signUp.SignUp();
 });

 Then(/^Verify Sign Up Screen elements$/, () => {
    return signUp.Verify_SignUp_Elements();
 });

 Then(/^Click SignUp$/,() => {
    return signUp.SignUpscreen();
 });
 
 Then(/^Add First Name$/, () => {
    return signUp.Add_First_Name(json.FirstName);
 });

 Then(/^Add Last Name$/, () => {
   return signUp.Add_Last_Name(json.LastName);
});

Then(/^Add Email$/, () => {
   return signUp.Add_Email();
});

Then(/^Add Phone$/, () => {
   return signUp.Add_Phone();
});

Then(/^Add Organisation$/, () => {
   return signUp.Select_Organisation();
});

Then(/^Add Position$/, () => {
   return signUp.Add_Position(json.Position);
});

Then(/^Add System Role$/, () => {
   return signUp.Add_System_Role();
});

Then(/^Click Next$/, () => {
   return signUp.Click_Next();
});

Then(/^Select Country$/, () => {
   return signUp.Select_Country();
});

Then(/^Enter Password$/, () => {
   return signUp.Enter_Password(json.Password);
});

Then(/^Enter Confirm Password$/, () => {
   return signUp.Enter_Confirm_Password(json.ConfirmPassword);
});

Then(/^Click Request Registration$/, () => {
   return signUp.Click_Request_Registration();
});