// It takes scenario steps and gets the entered title of the content.
const getTitleOfCreatedContent = steps => {
    const matchedStep = steps.find(step => {
      return step.text.includes('I should be able to see');
    });
  
    if (!matchedStep) {
      return 'Automation content created for';
    }
  
    const stepText = matchedStep.text.split('I should be able to see ')[1];
  
    return stepText.slice(0, stepText.length - 8).replace(/"/g, '');
  };
  
  const getFutureDate = (noOfDaysToAdd = 1) => {
    const date = new Date(
      new Date().getTime() + Number(noOfDaysToAdd) * 24 * 60 * 60 * 1000
    );
    const currentDate =
      date.getDate() > 10 ? date.getDate() : `0${date.getDate()}`;
    const month = date.getMonth() + 1;
  
    return `${date.getFullYear()}-${
      month < 10 ? `0${month}` : month
    }-${currentDate}`;
  };
  
  module.exports = { getTitleOfCreatedContent, getFutureDate };
  