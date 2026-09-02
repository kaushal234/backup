package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_ACE_2d1410 extends ItemConstraints {

  public IC_ACE_2d1410() {
    super();
  }

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_DOORCOL = features.get("DOORCOL").getString();
      String f_CAB = features.get("CAB").getString();

      s_display = 0;
      s_input = 0;
      f_DOORCOL = "";
      if( (f_CAB.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("DOORCOL").set(f_DOORCOL);
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

      String f_CABBUMP = features.get("CABBUMP").getString();
      String f_CAB = features.get("CAB").getString();

      s_display = 0;
      s_input = 0;
      f_CABBUMP = "";
      if( (f_CAB.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("CABBUMP").set(f_CABBUMP);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f003 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_HOODSTR = features.get("HOODSTR").getString();
      String f_CAB = features.get("CAB").getString();

      s_display = 0;
      s_input = 0;
      f_HOODSTR = "";
      if( (f_CAB.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("HOODSTR").set(f_HOODSTR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f004 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_WBTYPE = features.get("WBTYPE").getString();
      String f_WBSRVC = features.get("WBSRVC").getString();

      s_display = 0;
      s_input = 0;
      f_WBTYPE = "";
      if( (f_WBSRVC.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("WBTYPE").set(f_WBTYPE);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f005 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_LIFTBUMP = features.get("LIFTBUMP").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      s_display = 0;
      s_input = 0;
      f_LIFTBUMP = "";
      if( (f_WBTYPE.compareTo("LIFT") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("LIFTBUMP").set(f_LIFTBUMP);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f006 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_XTRFLOOD = features.get("XTRFLOOD").getString();
      String f_WBSRVC = features.get("WBSRVC").getString();

      s_display = 0;
      s_input = 0;
      f_XTRFLOOD = "";
      if( (f_WBSRVC.compareTo("N") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("XTRFLOOD").set(f_XTRFLOOD);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f007 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_MIRROR = features.get("MIRROR").getString();
      String f_CAB = features.get("CAB").getString();

      s_display = 0;
      s_input = 0;
      f_MIRROR = "";
      if( (f_CAB.compareTo("N") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("MIRROR").set(f_MIRROR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f008 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BEACON = features.get("BEACON").getString();

      s_display = 0;
      s_input = 0;
      f_BTYPE = "";
      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("BTYPE").set(f_BTYPE);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f009 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_BEACON = features.get("BEACON").getString();

      s_display = 0;
      s_input = 0;
      f_BCOLOR = "";
      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("BCOLOR").set(f_BCOLOR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f010 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_COLDPKG = features.get("COLDPKG").getString();

      s_display = 0;
      s_input = 0;
      f_COLDVOLT = "";
      if( (f_COLDPKG.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("COLDVOLT").set(f_COLDVOLT);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f011 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();

      s_display = 0;
      s_input = 0;
      f_BHTRVOLT = "";
      if( (f_BLKHTR.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("BHTRVOLT").set(f_BHTRVOLT);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");
      String s_message = globals.getString("message");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();

      if( (((((f_COLDVOLT.compareTo("110") == 0)) && ((f_BHTRVOLT.compareTo("110") == 0)))) || ((((f_COLDVOLT.compareTo("220") == 0)) && ((f_BHTRVOLT.compareTo("220") == 0))))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
        s_message = "Cold weather package and engine block heater must be the\nsame voltage";
      }

      globals.set("validate", s_validate);
      globals.set("message", s_message);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f012 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_FUELHTR = features.get("FUELHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_FUELHTR = "";
      if( (((f_ENGINE.compareTo("IZ") == 0)) || ((f_ENGINE.compareTo("PERKINS") == 0))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("FUELHTR").set(f_FUELHTR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f013 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_TOWTYPE = features.get("TOWTYPE").getString();
      String f_TOWPKG = features.get("TOWPKG").getString();

      s_display = 0;
      s_input = 0;
      f_TOWTYPE = "";
      if( (f_TOWPKG.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("TOWTYPE").set(f_TOWTYPE);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f014 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      String s_message = globals.getString("message");

      String f_LANG = features.get("LANG").getString();

      if( (f_LANG.compareTo("ZZZ") == 0) ) {
        s_message = "LANG";
      }

      globals.set("message", s_message);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f015 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      String s_message = globals.getString("message");

      s_message = "PAINT";

      globals.set("message", s_message);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f016 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_HOSEREEL = features.get("HOSEREEL").getString();
      String f_RJ = features.get("RJ").getString();

      s_display = 0;
      s_input = 0;
      f_HOSEREEL = "";
      if( (f_RJ.compareTo("N") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("HOSEREEL").set(f_HOSEREEL);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f017 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_THROTADV = features.get("THROTADV").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_THROTADV = "";
      if( (f_ENGINE.compareTo("FORD") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("THROTADV").set(f_THROTADV);
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

      String f_RJ = features.get("RJ").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_RJ.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i002 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RJ = features.get("RJ").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_RJ.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RJ = features.get("RJ").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_RJ.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i004 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RJ = features.get("RJ").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_RJ.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i005 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RJ = features.get("RJ").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_RJ.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i006 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RJ = features.get("RJ").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_RJ.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
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

      String f_CAB = features.get("CAB").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( (((((f_CAB.compareTo("Y") == 0)) && ((f_WBTYPE.compareTo("LIFT") == 0)))) || ((((f_CAB.compareTo("N") == 0)) && ((f_WBTYPE.compareTo("LIFT") != 0))))) ) {
        s_validate = 1;
      }
      else {
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
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((((f_ENGINE.compareTo("IZ") == 0)) || ((f_ENGINE.compareTo("PERKINS") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_HOSEREEL = features.get("HOSEREEL").getString();
      String f_RJ = features.get("RJ").getString();
      String f_WBSRVC = features.get("WBSRVC").getString();

      if( (((((f_HOSEREEL.compareTo("N") == 0)) && ((f_RJ.compareTo("N") == 0)))) && ((f_WBSRVC.compareTo("N") == 0))) ) {
        s_validate = 1;
      }
      else {
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

      String f_DUMPSTYL = features.get("DUMPSTYL").getString();
      String f_DUMPLOC = features.get("DUMPLOC").getString();

      if( !((f_DUMPSTYL.compareTo("OPEN") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_DUMPLOC.compareTo("REAR") == 0)) ) {
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

      String f_DUMPSTYL = features.get("DUMPSTYL").getString();
      String f_DUMPLOC = features.get("DUMPLOC").getString();

      if( !((f_DUMPSTYL.compareTo("OPEN") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_DUMPLOC.compareTo("SIDE") == 0)) ) {
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

      String f_DUMPSTYL = features.get("DUMPSTYL").getString();
      String f_DUMPLOC = features.get("DUMPLOC").getString();

      if( !((f_DUMPSTYL.compareTo("CLOSED") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_DUMPLOC.compareTo("REAR") == 0)) ) {
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

      String f_DUMPSTYL = features.get("DUMPSTYL").getString();
      String f_DUMPLOC = features.get("DUMPLOC").getString();

      if( !((f_DUMPSTYL.compareTo("CLOSED") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_DUMPLOC.compareTo("SIDE") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WBTYPE.compareTo("LIFT") != 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WBTYPE.compareTo("LIFT") != 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WBTYPE.compareTo("LIFT") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m014 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WBTYPE.compareTo("LIFT") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m015 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((f_ENGINE.compareTo("IZ") == 0)) || ((f_ENGINE.compareTo("PERKINS") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m019 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_DOORCOL = features.get("DOORCOL").getString();

      if( !((f_DOORCOL.compareTo("WHITE") == 0)) ) {
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

      String f_DOORCOL = features.get("DOORCOL").getString();

      if( !((f_DOORCOL.compareTo("BLUE") == 0)) ) {
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

      String f_CABBUMP = features.get("CABBUMP").getString();
      String f_BEACON = features.get("BEACON").getString();

      if( !((f_CABBUMP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BEACON.compareTo("N") == 0)) ) {
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

      String f_HOODSTR = features.get("HOODSTR").getString();

      if( !((f_HOODSTR.compareTo("Y") == 0)) ) {
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

      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_WBTYPE.compareTo("LIFT") == 0)) ) {
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

      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_WBTYPE.compareTo("LIFT") == 0)) ) {
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

      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_WBTYPE.compareTo("LADDER") == 0)) ) {
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

      String f_LIFTBUMP = features.get("LIFTBUMP").getString();

      if( !((f_LIFTBUMP.compareTo("Y") == 0)) ) {
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

      String f_UNITBUMP = features.get("UNITBUMP").getString();

      if( !((f_UNITBUMP.compareTo("Y") == 0)) ) {
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

      String f_XTRFLOOD = features.get("XTRFLOOD").getString();

      if( !((f_XTRFLOOD.compareTo("Y") == 0)) ) {
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

      String f_MIRROR = features.get("MIRROR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_MIRROR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m036 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m039 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m040 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m041 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m042 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m043 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_AUXPUMP = features.get("AUXPUMP").getString();

      if( !((f_AUXPUMP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m044 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BATTSW = features.get("BATTSW").getString();

      if( !((f_BATTSW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m045 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m046 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m047 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m048 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m049 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m050 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m051 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m052 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m053 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_COLDPKG = features.get("COLDPKG").getString();

      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m054 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_COLDPKG = features.get("COLDPKG").getString();

      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m055 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_COLDPKG = features.get("COLDPKG").getString();

      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m056 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_COLDPKG = features.get("COLDPKG").getString();

      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m057 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_FUELHTR = features.get("FUELHTR").getString();

      if( !((f_FUELHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m058 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_ENGSAFSD = features.get("ENGSAFSD").getString();

      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGSAFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m059 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_ENGSAFSD = features.get("ENGSAFSD").getString();

      if( !((f_COLDPKG.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGSAFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m060 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_FIREEXTG = features.get("FIREEXTG").getString();

      if( !((f_FIREEXTG.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m063 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_STORCOMP = features.get("STORCOMP").getString();

      if( !((f_STORCOMP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m065 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_MICO = features.get("MICO").getString();

      if( !((f_MICO.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m066 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_PERIMIND = features.get("PERIMIND").getString();

      if( !((f_PERIMIND.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m067 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_HOSEREEL = features.get("HOSEREEL").getString();

      if( !((f_HOSEREEL.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m068 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_PUMPSTRN = features.get("PUMPSTRN").getString();

      if( !((f_PUMPSTRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m069 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TOWTYPE = features.get("TOWTYPE").getString();

      if( !((f_TOWTYPE.compareTo("HOOK") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m070 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TOWTYPE = features.get("TOWTYPE").getString();

      if( !((f_TOWTYPE.compareTo("COUPLER") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m071 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TRANLOCK = features.get("TRANLOCK").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRANLOCK.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m072 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TRANLOCK = features.get("TRANLOCK").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRANLOCK.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m073 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_FLUSH = features.get("FLUSH").getString();

      if( !((f_FLUSH.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m074 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_PUMPSW = features.get("PUMPSW").getString();

      if( !((f_PUMPSW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m075 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_PUMPSW = features.get("PUMPSW").getString();

      if( !((f_PUMPSW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m076 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNDCOAT = features.get("UNDCOAT").getString();

      if( !((f_UNDCOAT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m078 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BRKCPLR = features.get("BRKCPLR").getString();
      String f_EXTCPLR = features.get("EXTCPLR").getString();

      if( !((f_BRKCPLR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_EXTCPLR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m079 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BRKCPLR = features.get("BRKCPLR").getString();
      String f_EXTCPLR = features.get("EXTCPLR").getString();

      if( !((f_BRKCPLR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_EXTCPLR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m080 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BRKCPLR = features.get("BRKCPLR").getString();
      String f_EXTCPLR = features.get("EXTCPLR").getString();

      if( !((f_BRKCPLR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_EXTCPLR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m081 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BRKCPLR = features.get("BRKCPLR").getString();
      String f_EXTCPLR = features.get("EXTCPLR").getString();

      if( !((f_BRKCPLR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_EXTCPLR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m082 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("1") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m083 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("2") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m084 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_RJ = features.get("RJ").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RJ.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m085 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_RJ = features.get("RJ").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RJ.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m087 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_DUMPSTYL = features.get("DUMPSTYL").getString();

      if( !((f_DUMPSTYL.compareTo("CLOSED") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m088 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_ENGSAFSD = features.get("ENGSAFSD").getString();

      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGSAFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m089 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_ENGSAFSD = features.get("ENGSAFSD").getString();

      if( !((f_COLDPKG.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGSAFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m090 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_SPEEDO = features.get("SPEEDO").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SPEEDO.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m091 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_SPEEDO = features.get("SPEEDO").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SPEEDO.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m092 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_HOSEREEL = features.get("HOSEREEL").getString();
      String f_RJ = features.get("RJ").getString();
      String f_WBSRVC = features.get("WBSRVC").getString();

      if( (((((f_HOSEREEL.compareTo("Y") == 0)) && ((f_RJ.compareTo("N") == 0)))) && ((f_WBSRVC.compareTo("N") == 0))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m093 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAB = features.get("CAB").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( (((f_CAB.compareTo("Y") == 0)) && ((f_WBTYPE.compareTo("LIFT") != 0))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m094 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((((f_ENGINE.compareTo("IZ") == 0)) || ((f_ENGINE.compareTo("PERKINS") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m095 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m096 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_HOSEREEL = features.get("HOSEREEL").getString();
      String f_RJ = features.get("RJ").getString();
      String f_WBSRVC = features.get("WBSRVC").getString();

      if( (((((f_HOSEREEL.compareTo("N") == 0)) && ((f_RJ.compareTo("Y") == 0)))) && ((f_WBSRVC.compareTo("N") == 0))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m098 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_HOSEREEL = features.get("HOSEREEL").getString();
      String f_RJ = features.get("RJ").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( (((((((f_HOSEREEL.compareTo("N") == 0)) && ((f_RJ.compareTo("N") == 0)))) && ((f_WBTYPE.compareTo("LIFT") == 0)))) || ((((((f_HOSEREEL.compareTo("N") == 0)) && ((f_RJ.compareTo("N") == 0)))) && ((f_WBTYPE.compareTo("LADDER") == 0))))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m099 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_HOSEREEL = features.get("HOSEREEL").getString();
      String f_RJ = features.get("RJ").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( (((((((f_HOSEREEL.compareTo("Y") == 0)) && ((f_RJ.compareTo("N") == 0)))) && ((f_WBTYPE.compareTo("LIFT") == 0)))) || ((((((f_HOSEREEL.compareTo("Y") == 0)) && ((f_RJ.compareTo("N") == 0)))) && ((f_WBTYPE.compareTo("LADDER") == 0))))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m100 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_HOSEREEL = features.get("HOSEREEL").getString();
      String f_RJ = features.get("RJ").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( (((((((f_HOSEREEL.compareTo("N") == 0)) && ((f_RJ.compareTo("Y") == 0)))) && ((f_WBTYPE.compareTo("LIFT") == 0)))) || ((((((f_HOSEREEL.compareTo("N") == 0)) && ((f_RJ.compareTo("Y") == 0)))) && ((f_WBTYPE.compareTo("LADDER") == 0))))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m102 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CABBUMP = features.get("CABBUMP").getString();
      String f_BEACON = features.get("BEACON").getString();

      if( !((f_CABBUMP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m103 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();
      String f_STORCOMP = features.get("STORCOMP").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_STORCOMP.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m104 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();
      String f_STORCOMP = features.get("STORCOMP").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_STORCOMP.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m105 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();
      String f_STORCOMP = features.get("STORCOMP").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_STORCOMP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m106 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();
      String f_STORCOMP = features.get("STORCOMP").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_STORCOMP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m107 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_COLDVOLT = features.get("COLDVOLT").getString();

      if( !((f_COLDPKG.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m108 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_COLDVOLT = features.get("COLDVOLT").getString();

      if( !((f_COLDPKG.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m109 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_RJ = features.get("RJ").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RJ.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m110 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_RJ = features.get("RJ").getString();

      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RJ.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m111 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_THROTADV = features.get("THROTADV").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_THROTADV.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("FORD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m112 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAB = features.get("CAB").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( (((f_CAB.compareTo("N") == 0)) && ((f_WBTYPE.compareTo("LIFT") == 0))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m113 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m114 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();
      String f_STORCOMP = features.get("STORCOMP").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_STORCOMP.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m115 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_CAB = features.get("CAB").getString();
      String f_STORCOMP = features.get("STORCOMP").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAB.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_STORCOMP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m116 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WBTYPE.compareTo("LIFT") != 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m117 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_WBTYPE = features.get("WBTYPE").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WBTYPE.compareTo("LIFT") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m118 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m119 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m120 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m121 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m122 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDVOLT = features.get("COLDVOLT").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_COLDVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BLKHTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m123 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_COLDPKG = features.get("COLDPKG").getString();

      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m124 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_COLDPKG = features.get("COLDPKG").getString();

      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m125 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_ENGSAFSD = features.get("ENGSAFSD").getString();

      if( !((f_COLDPKG.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGSAFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m126 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_COLDPKG = features.get("COLDPKG").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_ENGSAFSD = features.get("ENGSAFSD").getString();

      if( !((f_COLDPKG.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGSAFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m127 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TRANLOCK = features.get("TRANLOCK").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRANLOCK.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m128 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_RJ = features.get("RJ").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RJ.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m129 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_RJ = features.get("RJ").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RJ.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m130 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_SPEEDO = features.get("SPEEDO").getString();

      if( !((f_ENGINE.compareTo("PERKINS") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SPEEDO.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m064 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m077 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }
}
