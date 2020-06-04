## Variables for easier child theme customization
This is a non-exhaustive list of variables that child-themes could use for easier customization of Planet4 (see [PLANET-5114](https://jira.greenpeace.org/browse/PLANET-5114) for more details). Some of them are actually already in the code and could be used right away, but maybe they need to be renamed. Others need to be implemented, there is a follow-up ticket for this: [PLANET-5104](https://jira.greenpeace.org/browse/PLANET-5104).

### Already in place
#### Campaign colors
- Navigation bar background color: [--campaign_nav_color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_navbar.scss#L83)
- Footer background color: [--footer_color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_footer.scss#L47)
- Footer links color: [--footer_links_color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_footer.scss#L68)
#### Spreadsheet colors
- Spreadsheet header background color: [--spreadsheet-header-background](https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/master/assets/src/styles/blocks/Spreadsheet.scss#L27)
- Spreadsheet even rows background color: [--spreadsheet-even-row-background](https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/master/assets/src/styles/blocks/Spreadsheet.scss#L41)
- Spreadsheet odd rows background color: [--spreadsheet-odd-row-background](https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/master/assets/src/styles/blocks/Spreadsheet.scss#L51)

### Need to be created
These have been identified after checking some of the most customised Planet4 websites (Netherlands, Luxembourg, Switzerland). Their names can be defined once we come up with proper naming rules, and they link to places in the code where they should be used.

#### General
- [Body background](https://github.com/greenpeace/planet4-styleguide/blob/master/src/base/_body.scss#L11)

#### Navigation bar
- [Navigation bar link color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_navbar.scss#L100)
- [Navigation bar link hover color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_navbar.scss#L103): some NROs (i.e. Luxembourg) also set a background-color there, something we could add?
- [Navigation bar link active/focus/hover border-bottom](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_navbar.scss#L103)
- [Navigation bar active link color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_navbar.scss#L482)
- [Navigation bar active link border-bottom](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_navbar.scss#L483)
- [Donate button background color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/components/_buttons.scss#L165)
- [Donate button color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/components/_buttons.scss#L166)

#### Footer
- [Footer links hover color](https://github.com/greenpeace/planet4-styleguide/blob/master/src/layout/_footer.scss#L71)
