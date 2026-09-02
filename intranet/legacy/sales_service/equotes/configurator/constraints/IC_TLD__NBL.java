package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_TLD__NBL extends ItemConstraints {

  public IC_TLD__NBL() {
    super();
  }

  public static class c_M002 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();

      if( !((f_CONFIG.compareTo("NBL_EL") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_M003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();

      if( !((f_CONFIG.compareTo("NBL_TH") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_M001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_M004 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_M005 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_M006 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_M007 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }
}
