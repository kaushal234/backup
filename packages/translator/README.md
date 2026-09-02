[![Crowdin](https://badges.crowdin.net/alvest/localized.svg)](https://crowdin.com/project/alvest)

# Presentation
This component is used to centralize all translations of multiple projects.
We use [Crowdin](https://crowdin.com) to push and pull translations.

    1.Create a account on https://crowdin.com
    2.Go on (https://crowdin.com/project/alvest)
    3.Join project alvest

Crowndin is an external plateform to manage translations. It contains an Online Translation Editor.

# Installation
You maybe need to install your dependency to use this package in dev environment.
```
make translations-install-dev
```
Create an `.env.local` in `packages/translator/`. 

In your `.env.local` add:

```
CROWDIN_DSN=crowdin://554019:{TOKEN}@default
```

Finally, replace `{TOKEN}` by the one from passbolt.

# How to use it
We only push english translations to Crowdin. And pull french and chinese translations.
If you need to translate some words, you have to do it on Crowdin directly.
## Push translations
Execute this command after updating packages/translator/translations/en/*.en.yaml:
```
make translations-push
```

## Pull translations
After pushing translations, you need to update your french and chinese files:
```
make translations-pull
```
