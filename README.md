# README #

This project contains Behat contexts for Drupal.

### What is this repository for? ###

This Repository is used to extend the default Behat contexts provided by [Drupal Behat Extension](https://github.com/jhedstrom/drupalextension).

### How do I get set up? ###

Require it through composer and add the required contexts to your `behat.yml` file.

#### Context specific configuration options ####
Some contexts can be configured through the `behat.yml` file. These configuration options are documented below.

##### `\OrdinaDigitalServices\BrowserContext` #####
`BrowserContext` resizes the browser window to 1024x768 at the start of each scenario unless specified otherwise. This can be adjusted by adding some configuration in `behat.yml` like so (config file shortened for brevity):
````
default:
  suites:
    default:
      contexts:
        - ...
        - OrdinaDigitalServices\BrowserContext:
            resizeOnScenarioStart: false
            defaultWindowSize:
              width: 1920
              height: 1080
````
As you can see there are two options that can be set. A boolean `resizeOnScenarioStart` to disable the resizing altogether and an array `defaultWindowSize` to specify a different default width and/or height.