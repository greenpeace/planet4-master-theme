// https://github.com/conventional-changelog/commitlint/blob/master/docs/reference-rules.md
module.exports = {
  'rules': {
    'header-max-length': [2, 'always', 100],
    'header-min-length': [2, 'always', 10],
    'header-case': [2, 'always', 'sentence-case'],
    'header-full-stop': [2, 'never', '.'],
    'body-leading-blank': [2, 'always'],
    'body-empty': [2, 'never'],
    'body-case': [2, 'always', 'sentence-case']
  }
}
