const { client } = require('nightwatch-api');
const { Given, Then, When, And } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const category = client.page.categoryPage();

Given(/^Naviagte to Category Term$/, () => {
  return category.Navigate_to_Category_Page();  
});

Then(/^Select Category Country$/, () => {
  return category.select_country();
});

Then(/^Add Category Name$/, () => {
    return category.Add_Name(json.categoryName);
  });

Then(/^Add Category Description$/, () => {
    return category.Add_Description(json.categoryDescription);
  });

Then(/^Add Catgory Icon$/, () => {
    return category.Add_Icon();
  });

Then(/^Select Category Color$/, () => {
    return category.Select_Category_Color();
  });

Then(/^Add Discover Section Content$/, () => {
    return category.Add_Discover_Section_Content(json.discoverContent);
  });

Then(/^Click Category Save$/, () => {
    return category.Click_Save();
  });

Then(/^Navigate to created Category Term$/, () => {
    return category.verify_created_category_term();
  });

Then(/^Delete created Category Term$/, () => {
    return category.Delete_created_category_term();
  });