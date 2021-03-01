# Example of exported CSS variables

The 2 JSON files in this directory are an example of the export of all CSS variables in this repository and 
planet4-master-theme. Included both here to keep them together.

Each distinct variable has an entry with 2 properties: `usages` and `sourceUsages`.

`usages` is the result of taking all compiled CSS we load on the front end, and extracting all variables from it. It is
used in this branch to populate the theme editor.
* https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/bfd75cc104210089a7c99ec471bb829245c947d1/assets/src/theme/initializeThemeEditor.js#L33-L34
* https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/bfd75cc104210089a7c99ec471bb829245c947d1/assets/src/theme/getMatchingVars.js#L22 
Here we use the extracted selectors to check whether a variable would apply to the clicked element. This is done for
  each variable in the JSON, so that we end up with a list of variables that applies to the clicked element.

A tricky part is that in a lot of cases the variable occurs multiple times in the final CSS (because SASS expands, and
we also use certain variables in multiple places). Each usage has the selector and default value. The theme editor on
this branch simply picks the first usage to get the default value, since there is no way to know which one is the
"right" one. Eventually we should clean up the code so that each variable only ever uses a single default value (e.g.
currently setting the --footer-links-color will make all text in the footer be the same color, whereas by default
they're very different).

There's currently a lot of duplicate entries in the usages, for 2 reasons. 1) We scan all .css files that are built, but
some parts are repeated in those files. 2) @media queries and @supports are not detected as separate yet.

Since the line numbers are lost in the final CSS, we can't extract that info from the built CSS, and need to do a
separate export.

`sourceUsages` is the result of extracting the variables from the source SCCS. It's not used in the theme editor, but
could be useful if we want to set up a sync with for example the design system, which can then link to the source code
of each of the variable's usages.
