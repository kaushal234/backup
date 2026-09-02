package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_ACE_2dGPU extends ItemConstraints {

  public IC_ACE_2dGPU() {
    super();
  }

  public static class c_1 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_FWSEPTYP = features.get("FWSEPTYP").getString();
      String f_FWSEP = features.get("FWSEP").getString();

      s_display = 0;
      s_input = 0;
      f_FWSEPTYP = "";
      if( (f_FWSEP.compareTo("Y") == 0) ) {
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

  public static class c_2 implements ConstraintIF {
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

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");
      String s_message = globals.getString("message");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( (((((((((((((((f_OUTPUT.compareTo("090") == 0)) && ((f_GEN.compareTo("KATO") == 0)))) && ((((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("DUEMR") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))))) || ((((((f_OUTPUT.compareTo("090") == 0)) && ((f_GEN.compareTo("MAR") == 0)))) && ((((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("DUEMR") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))))))) || ((((((f_OUTPUT.compareTo("120") == 0)) && ((f_GEN.compareTo("KATO") == 0)))) && ((((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("DUEMR") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))))))) || ((((((f_OUTPUT.compareTo("120") == 0)) && ((f_GEN.compareTo("MAR") == 0)))) && ((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("DUEMR") == 0)))))))) || ((((((f_OUTPUT.compareTo("130") == 0)) && ((f_GEN.compareTo("KATO") == 0)))) && ((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))))))) || ((((((f_OUTPUT.compareTo("140") == 0)) && ((f_GEN.compareTo("KATO") == 0)))) && ((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0))))))) ) {
        s_validate = 1;
      }
      else {
        s_validate = 0;
        s_message = "That engine/generator/output combination is not available";
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

      String f_LFW = features.get("LFW").getString();
      String f_LFSD = features.get("LFSD").getString();

      s_display = 0;
      s_input = 0;
      f_LFW = "";
      if( (f_LFSD.compareTo("N") == 0) ) {
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

  public static class c_f005 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_LFSD = features.get("LFSD").getString();
      String f_LFW = features.get("LFW").getString();

      s_display = 0;
      s_input = 0;
      f_LFCOLOR = "";
      if( (((f_LFSD.compareTo("Y") == 0)) || ((f_LFW.compareTo("Y") == 0))) ) {
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

  public static class c_f006 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_RAILTYPE = features.get("RAILTYPE").getString();
      String f_RUBRAIL = features.get("RUBRAIL").getString();

      s_display = 0;
      s_input = 0;
      f_RAILTYPE = "";
      if( (f_RUBRAIL.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("RAILTYPE").set(f_RAILTYPE);
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

      String f_ETHER = features.get("ETHER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_ETHER = "";
      if( (((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("JD") == 0))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("ETHER").set(f_ETHER);
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

      String f_FWSEP = features.get("FWSEP").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_FWSEP = "";
      if( (((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("FWSEP").set(f_FWSEP);
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

      s_display = 0;
      s_input = 0;
      f_FWSEPTYP = "";
      if( (f_FWSEP.compareTo("Y") == 0) ) {
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

      String f_DRUMBRK = features.get("DRUMBRK").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      s_display = 0;
      s_input = 0;
      f_DRUMBRK = "";
      if( (f_TRAILER.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("DRUMBRK").set(f_DRUMBRK);
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

      String f_2NDOUT = features.get("2NDOUT").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();

      s_display = 0;
      s_input = 0;
      f_2NDOUT = "";
      if( (f_OUTPUT.compareTo("090") == 0) ) {
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

  public static class c_f012 implements ConstraintIF {
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

  public static class c_f013 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_GENWARN = features.get("GENWARN").getString();
      String f_GEN = features.get("GEN").getString();

      s_display = 0;
      s_input = 0;
      f_GENWARN = "";
      if( (f_GEN.compareTo("KATO") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("GENWARN").set(f_GENWARN);
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

  public static class c_f017 implements ConstraintIF {
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

      String f_28VTR = features.get("28VTR").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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

      String f_DRUMBRK = features.get("DRUMBRK").getString();

      if( !((f_DRUMBRK.compareTo("Y") == 0)) ) {
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

      String f_28VTR = features.get("28VTR").getString();

      if( !((f_28VTR.compareTo("N") == 0)) ) {
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

      String f_GEN = features.get("GEN").getString();

      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i007 implements ConstraintIF {
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

  public static class c_i008 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_28VTR = features.get("28VTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_i009 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_28VTR = features.get("28VTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_DRUMBRK = features.get("DRUMBRK").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( (((((((f_TRAILER.compareTo("Y") == 0)) && ((f_DRUMBRK.compareTo("N") == 0)))) && ((((((f_OUTPUT.compareTo("090") == 0)) || ((f_OUTPUT.compareTo("120") == 0)))) || ((f_OUTPUT.compareTo("130") == 0)))))) || ((((((((f_TRAILER.compareTo("Y") == 0)) && ((f_DRUMBRK.compareTo("N") == 0)))) && ((f_OUTPUT.compareTo("140") == 0)))) && ((f_ENGINE.compareTo("CU") == 0))))) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_DRUMBRK = features.get("DRUMBRK").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( (((((((f_TRAILER.compareTo("Y") == 0)) && ((f_DRUMBRK.compareTo("Y") == 0)))) && ((((((f_OUTPUT.compareTo("090") == 0)) || ((f_OUTPUT.compareTo("120") == 0)))) || ((f_OUTPUT.compareTo("130") == 0)))))) || ((((((((f_TRAILER.compareTo("Y") == 0)) && ((f_DRUMBRK.compareTo("Y") == 0)))) && ((f_OUTPUT.compareTo("140") == 0)))) && ((f_ENGINE.compareTo("CU") == 0))))) ) {
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

  public static class c_m003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_DRUMBRK = features.get("DRUMBRK").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_DRUMBRK.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_DRUMBRK = features.get("DRUMBRK").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_DRUMBRK.compareTo("Y") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( (((((f_OUTPUT.compareTo("090") == 0)) && ((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))))) || ((((((f_OUTPUT.compareTo("120") == 0)) && ((f_28VTR.compareTo("N") == 0)))) && ((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0))))))) ) {
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

  public static class c_m006 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( (((((f_OUTPUT.compareTo("090") == 0)) && ((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))))) || ((((((f_OUTPUT.compareTo("120") == 0)) && ((f_28VTR.compareTo("N") == 0)))) && ((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0))))))) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
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
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
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
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
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
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
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

  public static class c_m019 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((f_OUTPUT.compareTo("090") == 0)) || ((f_OUTPUT.compareTo("120") == 0)))) ) {
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

  public static class c_m021 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((f_OUTPUT.compareTo("090") == 0)) || ((f_OUTPUT.compareTo("120") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
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

  public static class c_m024 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
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

  public static class c_m027 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((f_OUTPUT.compareTo("130") == 0)) || ((f_OUTPUT.compareTo("140") == 0)))) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
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

  public static class c_m030 implements ConstraintIF {
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

  public static class c_m031 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m061 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m062 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m077 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m091 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

  public static class c_m093 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m095 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

  public static class c_m105 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m107 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

  public static class c_m109 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m111 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
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

  public static class c_m113 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m117 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m121 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m125 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
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

  public static class c_m130 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_2NDOUT = features.get("2NDOUT").getString();

      if( !((f_2NDOUT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m131 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
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

  public static class c_m132 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
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

  public static class c_m133 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
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

  public static class c_m134 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
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

  public static class c_m135 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
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

  public static class c_m136 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
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

  public static class c_m137 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m138 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m139 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
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

  public static class c_m140 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m141 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m142 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m143 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
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

  public static class c_m144 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m145 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_FWSEP = features.get("FWSEP").getString();
      String f_FWSEPTYP = features.get("FWSEPTYP").getString();

      if( !((f_FWSEP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FWSEPTYP.compareTo("DAVCO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m146 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_FWSEP = features.get("FWSEP").getString();
      String f_FWSEPTYP = features.get("FWSEPTYP").getString();

      if( !((f_FWSEP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FWSEPTYP.compareTo("RACORHTR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m147 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_FWSEP = features.get("FWSEP").getString();
      String f_FWSEPTYP = features.get("FWSEPTYP").getString();

      if( !((f_FWSEP.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FWSEPTYP.compareTo("RACOR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m148 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_DOWNEXH = features.get("DOWNEXH").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_DOWNEXH.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m149 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_DOWNEXH = features.get("DOWNEXH").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_DOWNEXH.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m151 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_DOWNEXH = features.get("DOWNEXH").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_DOWNEXH.compareTo("Y") == 0)) ) {
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

  public static class c_m152 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_DOWNEXH = features.get("DOWNEXH").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_DOWNEXH.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m153 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ETHER = features.get("ETHER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ETHER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m154 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ETHER = features.get("ETHER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ETHER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m155 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_FLOOD = features.get("FLOOD").getString();

      if( !((f_FLOOD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m156 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_GENWARN = features.get("GENWARN").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_GENWARN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m157 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_LFSD = features.get("LFSD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();

      if( !((f_LFSD.compareTo("Y") == 0)) ) {
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

  public static class c_m158 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_LFSD = features.get("LFSD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();

      if( !((f_LFSD.compareTo("Y") == 0)) ) {
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

  public static class c_m159 implements ConstraintIF {
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

  public static class c_m160 implements ConstraintIF {
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

  public static class c_m161 implements ConstraintIF {
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

  public static class c_m162 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_NBPT = features.get("NBPT").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();

      if( !((f_NBPT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("130") == 0)))) || ((f_OUTPUT.compareTo("140") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m163 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_NBPT = features.get("NBPT").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();

      if( !((f_NBPT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_OUTPUT.compareTo("090") == 0)) || ((f_OUTPUT.compareTo("100") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m164 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RUBRAIL = features.get("RUBRAIL").getString();
      String f_RAILTYPE = features.get("RAILTYPE").getString();

      if( !((f_RUBRAIL.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RAILTYPE.compareTo("BOTH") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m165 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RUBRAIL = features.get("RUBRAIL").getString();
      String f_RAILTYPE = features.get("RAILTYPE").getString();

      if( !((f_RUBRAIL.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RAILTYPE.compareTo("REAR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m166 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_RUBRAIL = features.get("RUBRAIL").getString();
      String f_RAILTYPE = features.get("RAILTYPE").getString();

      if( !((f_RUBRAIL.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_RAILTYPE.compareTo("SIDE") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m167 implements ConstraintIF {
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

  public static class c_m168 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TURBO = features.get("TURBO").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TURBO.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m169 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TURBO = features.get("TURBO").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TURBO.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("DU") == 0)) || ((f_ENGINE.compareTo("DUEMR") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m170 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TURBO = features.get("TURBO").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TURBO.compareTo("Y") == 0)) ) {
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

  public static class c_m171 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TURBO = features.get("TURBO").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TURBO.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m172 implements ConstraintIF {
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

      if( (((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("130") == 0)))) || ((f_OUTPUT.compareTo("140") == 0)))) || ((((f_OUTPUT.compareTo("090") == 0)) && ((f_2NDOUT.compareTo("Y") == 0))))) ) {
        s_quantity = 2;
        s_run_time = (s_run_time * 2);
        s_price = (s_price * 2);
      }

      globals.set("quantity", s_quantity);
      globals.set("run_time", s_run_time);
      globals.set("price", s_price);
    }
  }

  public static class c_m173 implements ConstraintIF {
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

      if( (((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("130") == 0)))) || ((f_OUTPUT.compareTo("140") == 0)))) || ((((f_OUTPUT.compareTo("090") == 0)) && ((f_2NDOUT.compareTo("Y") == 0))))) ) {
        s_quantity = 2;
        s_run_time = (s_run_time * 2);
        s_price = (s_price * 2);
      }

      globals.set("quantity", s_quantity);
      globals.set("run_time", s_run_time);
      globals.set("price", s_price);
    }
  }

  public static class c_m174 implements ConstraintIF {
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

      if( (((((((f_OUTPUT.compareTo("120") == 0)) || ((f_OUTPUT.compareTo("130") == 0)))) || ((f_OUTPUT.compareTo("140") == 0)))) || ((((f_OUTPUT.compareTo("090") == 0)) && ((f_2NDOUT.compareTo("Y") == 0))))) ) {
        s_quantity = 2;
        s_run_time = (s_run_time * 2);
        s_price = (s_price * 2);
      }

      globals.set("quantity", s_quantity);
      globals.set("run_time", s_run_time);
      globals.set("price", s_price);
    }
  }

  public static class c_m175 implements ConstraintIF {
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

  public static class c_m176 implements ConstraintIF {
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

  public static class c_m177 implements ConstraintIF {
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

  public static class c_m178 implements ConstraintIF {
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

  public static class c_m179 implements ConstraintIF {
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

  public static class c_m180 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m181 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m182 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m183 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m184 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m185 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m186 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m187 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m188 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m189 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m190 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m191 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m192 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m193 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m194 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m195 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m196 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m197 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m198 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m199 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m200 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m201 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m202 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m203 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m204 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m205 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m206 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m207 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m208 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m209 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m210 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m211 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m212 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m213 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m214 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m215 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("090") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m236 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m237 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m238 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m239 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m240 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m241 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m242 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m243 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m244 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m245 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m246 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m247 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m248 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m249 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m250 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m251 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m252 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m253 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m254 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m255 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m256 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m257 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m258 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m259 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m260 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m261 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m262 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m263 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("MAR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m264 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m265 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m266 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m267 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m274 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m275 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m276 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m277 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("130") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m284 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m285 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m286 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m287 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m292 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m293 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
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

  public static class c_m294 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m295 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
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

  public static class c_m300 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("90") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m301 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("90") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m302 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("90") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m303 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("90") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m304 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m305 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m306 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m307 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_GEN = features.get("GEN").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m308 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("90") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m309 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("90") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m310 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m311 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DUEMR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m312 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TRAILER = features.get("TRAILER").getString();
      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( (((((f_TRAILER.compareTo("N") == 0)) && ((((((f_OUTPUT.compareTo("090") == 0)) || ((f_OUTPUT.compareTo("120") == 0)))) || ((f_OUTPUT.compareTo("130") == 0)))))) || ((((((f_TRAILER.compareTo("N") == 0)) && ((f_OUTPUT.compareTo("140") == 0)))) && ((f_ENGINE.compareTo("CU") == 0))))) ) {
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

  public static class c_m313 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_OUTPUT.compareTo("140") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m314 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m315 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_OUTPUT = features.get("OUTPUT").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_28VTR = features.get("28VTR").getString();
      String f_GEN = features.get("GEN").getString();

      if( !((f_OUTPUT.compareTo("120") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_28VTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_GEN.compareTo("KATO") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_1 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_2 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m129 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }
}
