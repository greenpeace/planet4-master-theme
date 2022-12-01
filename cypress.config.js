module.exports = {
  "e2e": {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
    baseUrl: "https://www.planet4.test",
  },
  "retries": {
    // Configure retry attempts for `cypress run`
    // Default is 0
    "runMode": 2,
    // Configure retry attempts for `cypress open`
    // Default is 0
    "openMode": 0
  }
};
