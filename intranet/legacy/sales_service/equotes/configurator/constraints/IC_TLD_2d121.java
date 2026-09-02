package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_TLD_2d121 extends ItemConstraints {

  public IC_TLD_2d121() {
    super();
  }

  public static class c_f01 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ELEV = features.get("ELEV").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_ELEV = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ELEV").set(f_ELEV);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f02 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_ENGINE = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ENGINE").set(f_ENGINE);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f03 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_PUMP = features.get("PUMP").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_PUMP = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("PUMP").set(f_PUMP);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f04 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_FUELTK = features.get("FUELTK").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_FUELTK = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("FUELTK").set(f_FUELTK);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f05 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_OPCONS = features.get("OPCONS").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_OPCONS = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("OPCONS").set(f_OPCONS);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f06 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_JOYSTK = features.get("JOYSTK").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_JOYSTK = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("JOYSTK").set(f_JOYSTK);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f07 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_GUARDR = features.get("GUARDR").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_GUARDR = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("GUARDR").set(f_GUARDR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f08 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_WEATHR = features.get("WEATHR").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_WEATHR = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("WEATHR").set(f_WEATHR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f09 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_LANGU = features.get("LANGU").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_LANGU = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("LANGU").set(f_LANGU);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f10 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_CUSTOPT = features.get("CUSTOPT").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_CUSTOPT = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("CUSTOPT").set(f_CUSTOPT);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f11 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_OVERSEAS = features.get("OVERSEAS").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_OVERSEAS = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("OVERSEAS").set(f_OVERSEAS);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f12 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ADDOPT = features.get("ADDOPT").getString();
      String f_CONFIG = features.get("CONFIG").getString();

      s_display = 0;
      s_input = 0;
      f_ADDOPT = "";
      if( (f_CONFIG.compareTo("STD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ADDOPT").set(f_ADDOPT);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f13 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZPROTAN = features.get("ZPROTAN").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZPROTAN = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZPROTAN").set(f_ZPROTAN);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f14 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZTACH = features.get("ZTACH").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZTACH = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZTACH").set(f_ZTACH);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f15 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_Z110COLD = features.get("Z110COLD").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_Z110COLD = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("Z110COLD").set(f_Z110COLD);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f16 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_Z220COLD = features.get("Z220COLD").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_Z220COLD = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("Z220COLD").set(f_Z220COLD);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f17 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZARTIC = features.get("ZARTIC").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZARTIC = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZARTIC").set(f_ZARTIC);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f18 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZSPARK = features.get("ZSPARK").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZSPARK = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZSPARK").set(f_ZSPARK);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f19 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZARMBUMP = features.get("ZARMBUMP").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZARMBUMP = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZARMBUMP").set(f_ZARMBUMP);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f20 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_Z2DOOR = features.get("Z2DOOR").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_Z2DOOR = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("Z2DOOR").set(f_Z2DOOR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f21 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZENLIGHT = features.get("ZENLIGHT").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZENLIGHT = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZENLIGHT").set(f_ZENLIGHT);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f22 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZAUTOLEV = features.get("ZAUTOLEV").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZAUTOLEV = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZAUTOLEV").set(f_ZAUTOLEV);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f23 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZGREYCON = features.get("ZGREYCON").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZGREYCON = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZGREYCON").set(f_ZGREYCON);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f24 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZAMB = features.get("ZAMB").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZAMB = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZAMB").set(f_ZAMB);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f25 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZHORN = features.get("ZHORN").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZHORN = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZHORN").set(f_ZHORN);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f26 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZFLBRIDG = features.get("ZFLBRIDG").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZFLBRIDG = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZFLBRIDG").set(f_ZFLBRIDG);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f27 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZFLELEV = features.get("ZFLELEV").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZFLELEV = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZFLELEV").set(f_ZFLELEV);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f28 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ZREBRSWI = features.get("ZREBRSWI").getString();
      String f_ADDOPT = features.get("ADDOPT").getString();

      s_display = 0;
      s_input = 0;
      f_ZREBRSWI = "";
      if( (f_ADDOPT.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ZREBRSWI").set(f_ZREBRSWI);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m004 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CAT") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m005 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_PUMP = features.get("PUMP").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PUMP.compareTo("REX") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m006 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_PUMP = features.get("PUMP").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PUMP.compareTo("KAWA") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m007 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_FUELTK = features.get("FUELTK").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELTK.compareTo("60") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m008 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_FUELTK = features.get("FUELTK").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELTK.compareTo("60") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m009 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_OPCONS = features.get("OPCONS").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_OPCONS.compareTo("POW") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m010 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_ELEV = features.get("ELEV").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ELEV.compareTo("SR") == 0)) || ((f_ELEV.compareTo("DR") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m011 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_ELEV = features.get("ELEV").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ELEV.compareTo("DR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m012 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_ELEV = features.get("ELEV").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ELEV.compareTo("SR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m013 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_GUARDR = features.get("GUARDR").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GUARDR.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m016 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_LANGU = features.get("LANGU").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LANGU.compareTo("ZZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m017 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_WEATHR = features.get("WEATHR").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WEATHR.compareTo("REGULAR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m018 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OVERSEAS = features.get("OVERSEAS").getString();

      if( !((f_OVERSEAS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m020 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZPROTAN = features.get("ZPROTAN").getString();

      if( !((f_ZPROTAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m021 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZTACH = features.get("ZTACH").getString();

      if( !((f_ZTACH.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m022 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_Z110COLD = features.get("Z110COLD").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_Z110COLD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m023 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_Z220COLD = features.get("Z220COLD").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_Z220COLD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m024 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZARTIC = features.get("ZARTIC").getString();

      if( !((f_ZARTIC.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m025 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZSPARK = features.get("ZSPARK").getString();

      if( !((f_ZSPARK.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m026 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZBUMPER = features.get("ZBUMPER").getString();

      if( !((f_ZBUMPER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m027 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_Z2DOOR = features.get("Z2DOOR").getString();

      if( !((f_Z2DOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m028 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZENLIGHT = features.get("ZENLIGHT").getString();

      if( !((f_ZENLIGHT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m029 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZAUTOLEV = features.get("ZAUTOLEV").getString();

      if( !((f_ZAUTOLEV.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m030 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZGREYCON = features.get("ZGREYCON").getString();

      if( !((f_ZGREYCON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m031 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZAMB = features.get("ZAMB").getString();

      if( !((f_ZAMB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m032 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZHORN = features.get("ZHORN").getString();

      if( !((f_ZHORN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m033 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZFLELEV = features.get("ZFLELEV").getString();
      String f_OPCONS = features.get("OPCONS").getString();

      if( !((f_ZFLELEV.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_OPCONS.compareTo("POW") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m034 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZFLBRIDG = features.get("ZFLBRIDG").getString();

      if( !((f_ZFLBRIDG.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m035 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ZREBRSWI = features.get("ZREBRSWI").getString();

      if( !((f_ZREBRSWI.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m037 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_FUELTK = features.get("FUELTK").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELTK.compareTo("60") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m038 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CONFIG = features.get("CONFIG").getString();
      String f_FUELTK = features.get("FUELTK").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_CONFIG.compareTo("STD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELTK.compareTo("60") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CAT") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m002 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m014 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m036 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }
}
