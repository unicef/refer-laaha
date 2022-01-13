const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const tour = client.page.onboardingTourPage();

Given(/^Navigate to the Tour screen$/, () => {
    return tour.Navigate_To_Tour_Screen();  
  });
  
  Then(/^Add Tour$/, () => {
    return tour.Verify_Click_Add_Tour_Button();
  });

  Then(/^Verify Add Tour Screen$/, () => {
    return tour.Verify_Add_tour_Screen();
  });

  Then(/^Add Tour Name$/, () => {
    // var randomstring = json.TourName
    return tour.Add_Tour_Name();
  });

  Then(/^Add Module Name$/, () => {
    return tour.Add_Module_Name(json.ModuleName);
  });

  Then(/^Add Routes$/, () => {
    return tour.Add_Routes(json.Routes);
  });

  Then(/^Save Tour$/, () => {
    return tour.Save_Tour();
  }); 

  Then(/^Select Text Label from Dropdown$/, () => {
    return tour.SelectText();
  });

  Then(/^Click on Add and Verify Tip Screen elements$/, () => {
    return tour.Click_On_Add_Button();
  });
  
  Then(/^Add Label$/, () => {
    return tour.Add_Label();
  });

  Then(/^Add Selector$/, () => {
    return tour.Add_Selector(json.Selector);
  });

  Then(/^Select Position$/, () => {
    return tour.Add_Position();
  });

  Then(/^Add text in Body$/, () => {
    return tour.Body(json.Body);
  });

  Then(/^Verify Tour in added Node$/, () => {
    return tour.Verify_Node();
  });

  Then(/^Delete Created Tour$/, () => {
    return tour.Delete_created_Tour();
  });