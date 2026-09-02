package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_LSP_2dWSP extends ItemConstraints {

  public IC_LSP_2dWSP() {
    super();
  }

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_CABIN = features.get("CABIN").getString();

      s_display = 0;
      s_input = 0;
      f_CABDOOR = "";
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("CABDOOR").set(f_CABDOOR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f002 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_CABHEAT = features.get("CABHEAT").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();

      s_display = 0;
      s_input = 0;
      f_CABHEAT = "";
      if( (f_CABDOOR.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("CABHEAT").set(f_CABHEAT);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITTYPE = features.get("UNITTYPE").getString();

      if( !((f_UNITTYPE.compareTo("LSP-V") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITTYPE = features.get("UNITTYPE").getString();

      if( !((f_UNITTYPE.compareTo("WSP") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o002 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITTYPE = features.get("UNITTYPE").getString();

      if( !((f_UNITTYPE.compareTo("LSP") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITTYPE = features.get("UNITTYPE").getString();

      if( !((f_UNITTYPE.compareTo("LSP-V") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o004 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITTYPE = features.get("UNITTYPE").getString();

      if( !((f_UNITTYPE.compareTo("WSP") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o005 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITTYPE = features.get("UNITTYPE").getString();

      if( !((f_UNITTYPE.compareTo("LSP") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o006 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITTYPE = features.get("UNITTYPE").getString();

      if( !((f_UNITTYPE.compareTo("LSP-V") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }
}
