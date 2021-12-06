const reporter = require('cucumber-junit-convert');

const {
  setDefaultTimeout,
  After,
  Before,
  AfterAll,
  BeforeAll
} = require('@cucumber/cucumber');

const {
  client,
  createSession,
  closeSession,
  startWebDriver,
  stopWebDriver,
  getNewScreenshots
} = require('nightwatch-api');

const sampleContent = require('./Data/components.json');
const utils = require('./utils/index.js');

// function to delete created content
const deleteContent = async title => {
  const global = client.page.globalPage();

  await global.openContentMenu();
  await global.verifyTheContentPage(sampleContent.global.contentHeading);
  await global.deleteCreatedContent(title);
};

setDefaultTimeout(90000);

BeforeAll(async () => {
  await startWebDriver();
  await createSession();
});

Before(
  () =>
    new Promise(resolve => {
      setTimeout(async () => {
        await createSession();
        client.maximizeWindow();
        resolve();
      }, 1000);
    })
);

AfterAll(async () => {
  setTimeout(() => {
    const options = {
      inputJsonFile: 'reports/cucumber_report.json',
      outputXmlFile: 'reports/cucumber_report.xml'
    };
    reporter.convert(options);
  }, 1000);
  await stopWebDriver();
});

After(async result => {
  if (result.pickle.name == '2. Verify the added content') {
    const title = utils.getTitleOfCreatedContent(result.pickle.steps);
    // deleting the content after verification
    await deleteContent(title);
  }

  return new Promise(resolve => {
    setTimeout(async () => {
      await closeSession();
      resolve();
    }, 1000);
  });
});
