<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;

/**
 * Class SmokeTestContext.
 */
class SmokeTestContext implements Context {

  use UsesMink;

  /**
   * @Given I can execute javascript
   *
   * @throws \Exception
   */
  public function iCanExecuteJavascript(): void {
    $value = $this->getSession()->evaluateScript("return 'test'");
    if ($value !== 'test') {
      throw new \Exception("Could not evaluate javascript");
    }
  }

}
