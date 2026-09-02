package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_ACE_2d4000E extends ItemConstraints {

  public IC_ACE_2d4000E() {
    super();
  }

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");
      String s_message = globals.getString("message");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( (((((((((((((((((f_OUTPUT.compareTo("090") == 0)) && ((f_GEN.compareTo("ACE") == 0)))) && ((f_ENGINE.compareTo("DU") == 0)))) || ((((((f_OUTPUT.compareTo("090") == 0)) && ((f_GEN.compareTo("KATO") == 0)))) && ((f_ENGINE.compareTo("DU") == 0)))))) || ((((((f_OUTPUT.compareTo("090") == 0)) && ((f_GEN.compareTo("MAR") == 0)))) && ((f_ENGINE.compareTo("DU") == 0)))))) || ((((((f_OUTPUT.compareTo("120") == 0)) && ((f_GEN.compareTo("ACE") == 0)))) && ((f_ENGINE.compareTo("DU") == 0)))))) || ((((((f_OUTPUT.compareTo("120") == 0)) && ((f_GEN.compareTo("KATO") == 0)))) && ((f_ENGINE.compareTo("DU") == 0)))))) || ((((((f_OUTPUT.compareTo("120") == 0)) && ((f_GEN.compareTo("MAR") == 0)))) && ((f_ENGINE.compareTo("DU") == 0)))))) || ((((((f_OUTPUT.compareTo("140") == 0)) && ((f_GEN.compareTo("KATO") == 0)))) && ((f_ENGINE.compareTo("DU") == 0))))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
        s_message = "That engine/generator/output combination is not currently\navailable, please consult factory.";
      }

      globals.set("validate", s_validate);
      globals.set("message", s_message);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f002 implements ConstraintIF {
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

  public static class c_f003 implements ConstraintIF {
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

  public static class c_f004 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_LFRD = features.get("LFRD").getString();
      String f_LFSD = features.get("LFSD").getString();

      s_display = 0;
      s_input = 0;
      f_LFRD = "";
      if( (f_LFSD.compareTo("N") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("LFRD").set(f_LFRD);
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

      String f_LFW = features.get("LFW").getString();
      String f_LFSD = features.get("LFSD").getString();
      String f_LFRD = features.get("LFRD").getString();

      s_display = 0;
      s_input = 0;
      f_LFW = "";
      if( (((f_LFSD.compareTo("N") == 0)) && ((f_LFRD.compareTo("N") == 0))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("LFW").set(f_LFW);
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

      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_LFSD = features.get("LFSD").getString();
      String f_LFRD = features.get("LFRD").getString();
      String f_LFW = features.get("LFW").getString();

      s_display = 0;
      s_input = 0;
      f_LFCOLOR = "";
      if( (((((f_LFSD.compareTo("Y") == 0)) || ((f_LFRD.compareTo("Y") == 0)))) || ((f_LFW.compareTo("Y") == 0))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("LFCOLOR").set(f_LFCOLOR);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");
      String s_message = globals.getString("message");

      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( (((((f_LFCOLOR.compareTo("R") == 0)) && ((f_BCOLOR.compareTo("R") == 0)))) || ((((f_LFCOLOR.compareTo("A") == 0)) && ((f_BCOLOR.compareTo("A") == 0))))) ) {
        s_validate = 0;
        s_message = "BEACON";
      }
      else {
        s_validate = 1;
      }

      globals.set("validate", s_validate);
      globals.set("message", s_message);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f007 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      s_display = 0;
      s_input = 0;

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

      s_display = 0;
      s_input = 0;

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

      String f_FWSEPTYP = features.get("FWSEPTYP").getString();
      String f_FWSEP = features.get("FWSEP").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();

      s_display = 0;
      s_input = 0;
      f_FWSEPTYP = "";
      if( (((f_FWSEP.compareTo("Y") == 0)) && ((f_FUELHTR.compareTo("N") == 0))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("FWSEPTYP").set(f_FWSEPTYP);
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

      String f_2NDOUT = features.get("2NDOUT").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();

      s_display = 0;
      s_input = 0;
      f_2NDOUT = "";
      if( (f_OUTPUT.compareTo("90") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("2NDOUT").set(f_2NDOUT);
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
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f012 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_NBPT = features.get("NBPT").getString();
      String f_GEN = features.get("GEN").getString();

      s_display = 0;
      s_input = 0;
      f_NBPT = "";
      if( (f_GEN.compareTo("KATO") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("NBPT").set(f_NBPT);
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

  public static class c_f014 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      String s_message = globals.getString("message");

      String f_PAINTCOL = features.get("PAINTCOL").getString();

      if( (f_PAINTCOL.compareTo("ZZZ") == 0) ) {
        s_message = "PAINT";
      }

      globals.set("message", s_message);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f015 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_28CABLE = features.get("28CABLE").getString();
      String f_28VTR = features.get("28VTR").getString();

      s_display = 0;
      s_input = 0;
      f_28CABLE = "";
      if( (f_28VTR.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("28CABLE").set(f_28CABLE);
      globals.set("input", s_input);
      globals.set("display", s_display);
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_f016 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_LCOOLSD = features.get("LCOOLSD").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_LCOOLSD = "";
      if( (f_ENGINE.compareTo("DU") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("LCOOLSD").set(f_LCOOLSD);
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

      String f_FUELHTR = features.get("FUELHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_FWSEP = features.get("FWSEP").getString();

      s_display = 0;
      s_input = 0;
      f_FUELHTR = "";
      if( (((f_ENGINE.compareTo("DU") == 0)) || ((f_FWSEP.compareTo("Y") == 0))) ) {
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

  public static class c_f018 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_FWSEPHTR = features.get("FWSEPHTR").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();

      s_display = 0;
      s_input = 0;
      f_FWSEPHTR = "";
      if( (f_FUELHTR.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("FWSEPHTR").set(f_FWSEPHTR);
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

      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_GEN = features.get("GEN").getString();

      if( !((f_GEN.compareTo("ACE") == 0)) ) {
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

      String f_GEN = features.get("GEN").getString();

      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_GEN = features.get("GEN").getString();

      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_2NDOUT.compareTo("N") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
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
      String f_TRAILER = features.get("TRAILER").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELHTR.compareTo("N") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
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

      String f_GEN = features.get("GEN").getString();

      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_GEN = features.get("GEN").getString();

      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_GEN = features.get("GEN").getString();

      if( !((f_GEN.compareTo("ACE") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0)))) ) {
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

  public static class c_m011 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_2NDOUT.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_2NDOUT.compareTo("N") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();

      if( !((f_28VTR.compareTo("N") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
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

      String f_LFSD = features.get("LFSD").getString();
      String f_LFRD = features.get("LFRD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();

      if( !((((f_LFSD.compareTo("Y") == 0)) || ((f_LFRD.compareTo("Y") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("R") == 0)) ) {
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

      String f_LFSD = features.get("LFSD").getString();
      String f_LFRD = features.get("LFRD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();

      if( !((((f_LFSD.compareTo("Y") == 0)) || ((f_LFRD.compareTo("Y") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("A") == 0)) ) {
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

      String f_LFW = features.get("LFW").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();

      if( !((f_LFW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("R") == 0)) ) {
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

      String f_LFW = features.get("LFW").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();

      if( !((f_LFW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("A") == 0)) ) {
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

      String f_LCOOLSD = features.get("LCOOLSD").getString();

      if( !((f_LCOOLSD.compareTo("Y") == 0)) ) {
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

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("120") == 0)) ) {
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

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
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

      String f_EXTRATTN = features.get("EXTRATTN").getString();

      if( !((f_EXTRATTN.compareTo("Y") == 0)) ) {
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

      String f_FORKPOCK = features.get("FORKPOCK").getString();

      if( !((f_FORKPOCK.compareTo("Y") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_28VTR.compareTo("N") == 0)) ) {
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

  public static class c_m033 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_28VTR = features.get("28VTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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

  public static class c_m034 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_400CABLE = features.get("400CABLE").getString();

      if( !((f_400CABLE.compareTo("30") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      double s_quantity = globals.getDouble("quantity");
      int s_run_time = globals.getInteger("run_time");
      double s_price = globals.getDouble("price");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( (((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0))) ) {
        s_quantity = 2;
        s_run_time = (s_run_time * 2);
        s_price = (s_price * 2);
      }

      globals.set("quantity", s_quantity);
      globals.set("run_time", s_run_time);
      globals.set("price", s_price);
    }
  }

  public static class c_m035 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_400CABLE = features.get("400CABLE").getString();

      if( !((f_400CABLE.compareTo("40") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      double s_quantity = globals.getDouble("quantity");
      int s_run_time = globals.getInteger("run_time");
      double s_price = globals.getDouble("price");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( (((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0))) ) {
        s_quantity = 2;
        s_run_time = (s_run_time * 2);
        s_price = (s_price * 2);
      }

      globals.set("quantity", s_quantity);
      globals.set("run_time", s_run_time);
      globals.set("price", s_price);
    }
  }

  public static class c_m036 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_400CABLE = features.get("400CABLE").getString();

      if( !((f_400CABLE.compareTo("50") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      double s_quantity = globals.getDouble("quantity");
      int s_run_time = globals.getInteger("run_time");
      double s_price = globals.getDouble("price");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( (((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0))) ) {
        s_quantity = 2;
        s_run_time = (s_run_time * 2);
        s_price = (s_price * 2);
      }

      globals.set("quantity", s_quantity);
      globals.set("run_time", s_run_time);
      globals.set("price", s_price);
    }
  }

  public static class c_m037 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_28CABLE = features.get("28CABLE").getString();

      if( !((f_28CABLE.compareTo("30") == 0)) ) {
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

      String f_28CABLE = features.get("28CABLE").getString();

      if( !((f_28CABLE.compareTo("40") == 0)) ) {
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

      String f_28CABLE = features.get("28CABLE").getString();

      if( !((f_28CABLE.compareTo("50") == 0)) ) {
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

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("1") == 0)) ) {
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

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("2") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0)))) ) {
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

      String f_28VTR = features.get("28VTR").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) || ((f_2NDOUT.compareTo("Y") == 0)))) ) {
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

      String f_28VTR = features.get("28VTR").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_2NDOUT.compareTo("N") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();
      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_2NDOUT.compareTo("N") == 0)) ) {
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

      String f_NBPT = features.get("NBPT").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();

      if( !((f_NBPT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) ) {
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

      String f_NBPT = features.get("NBPT").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();

      if( !((f_NBPT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }
}
