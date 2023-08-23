const path = require('path')
const { CLIEngine } = require('eslint')

const relativePaths = (files) => files.map((file) => path.relative(process.cwd(), file))

module.exports = {
  "*.js": (files) => {
    const cli = new CLIEngine({})
    return 'eslint --fix --max-warnings=0 ' + files.filter((file) => !cli.isPathIgnored(file)).join(' ')
  },
  "*.php": (files) => {
    return [
      `npm run lint:php:fix -- ${relativePaths(files).join(' ')}`,
      `npm run lint:php -- ${relativePaths(files).join(' ')}`
    ]
  }
}
