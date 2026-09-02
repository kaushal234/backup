package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_ACE_2d804 extends ItemConstraints {

  public IC_ACE_2d804() {
    super();
  }

  public static class c_p001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_AIRDELHS = features.get("AIRDELHS").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_p002 implements ConstraintIF {
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

  public static class c_p003 implements ConstraintIF {
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

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_TRLRTYPE = "";
      if( (((f_TRAILER.compareTo("Y") == 0)) && ((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0))))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("TRLRTYPE").set(f_TRLRTYPE);
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

      String f_SLDTIRE = features.get("SLDTIRE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_SLDTIRE = "";
      if( (((f_TRAILER.compareTo("Y") == 0)) && ((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0))))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("SLDTIRE").set(f_SLDTIRE);
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

      String f_TOWBRK = features.get("TOWBRK").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_SLDTIRE = features.get("SLDTIRE").getString();

      s_display = 0;
      s_input = 0;
      f_TOWBRK = "";
      if( (((((f_TRAILER.compareTo("Y") == 0)) && ((f_TRLRTYPE.compareTo("FIFTH") == 0)))) && ((f_SLDTIRE.compareTo("N") == 0))) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("TOWBRK").set(f_TOWBRK);
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

  public static class c_f005 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_ETHER = features.get("ETHER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_ETHER = "";
      if( (((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("JD") == 0)))) || ((f_ENGINE.compareTo("CU24") == 0))) ) {
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

  public static class c_f006 implements ConstraintIF {
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

  public static class c_f007 implements ConstraintIF {
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

      String f_LFW = features.get("LFW").getString();
      String f_LFSD = features.get("LFSD").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      s_display = 0;
      s_input = 0;
      f_LFW = "";
      if( (((f_LFSD.compareTo("N") == 0)) && ((f_TRAILER.compareTo("Y") == 0))) ) {
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

  public static class c_f011 implements ConstraintIF {
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

  public static class c_f012 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_AIRDELHS = features.get("AIRDELHS").getString();

      s_display = 0;
      s_input = 0;
      f_HOSETYPE = "";
      if( (f_AIRDELHS.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("HOSETYPE").set(f_HOSETYPE);
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

      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_AIRDELHS = features.get("AIRDELHS").getString();

      s_display = 0;
      s_input = 0;
      f_INSLHOSE = "";
      if( (f_AIRDELHS.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("INSLHOSE").set(f_INSLHOSE);
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

      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_AIRDELHS = features.get("AIRDELHS").getString();

      s_display = 0;
      s_input = 0;
      f_HOSELGTH = "";
      if( (f_AIRDELHS.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("HOSELGTH").set(f_HOSELGTH);
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

  public static class c_f016 implements ConstraintIF {
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

  public static class c_f017 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_FUELHTR = features.get("FUELHTR").getString();
      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      s_display = 0;
      s_input = 0;
      f_FUELHTR = "";
      if( (((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) && ((f_UNITYPE.compareTo("HEATCOOL") == 0))) ) {
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

      String f_LFSD = features.get("LFSD").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      s_display = 0;
      s_input = 0;
      f_LFSD = "";
      if( (f_TRAILER.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("LFSD").set(f_LFSD);
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("FIFTH") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_SLDTIRE = features.get("SLDTIRE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("FIFTH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SLDTIRE.compareTo("N") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_SLDTIRE = features.get("SLDTIRE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("ACKERMAN") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SLDTIRE.compareTo("N") == 0)) ) {
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

      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
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

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
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

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
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

      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
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
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m015 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m016 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m017 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("CU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m018 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m019 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m020 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m021 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m022 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m023 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m024 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m025 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m026 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m027 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m028 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m029 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();

      if( !((f_ENGINE.compareTo("JD") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m030 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_TOWBRK = features.get("TOWBRK").getString();

      if( !((f_TOWBRK.compareTo("Y") == 0)) ) {
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

      String f_SLDTIRE = features.get("SLDTIRE").getString();

      if( !((f_SLDTIRE.compareTo("Y") == 0)) ) {
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

      String f_AUTODEF = features.get("AUTODEF").getString();

      if( !((f_AUTODEF.compareTo("Y") == 0)) ) {
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

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

  public static class c_m043 implements ConstraintIF {
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

  public static class c_m044 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_BEACON = features.get("BEACON").getString();
      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BEACON.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

  public static class c_m051 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TURBO = features.get("TURBO").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TURBO.compareTo("Y") == 0)) ) {
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

  public static class c_m052 implements ConstraintIF {
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

  public static class c_m053 implements ConstraintIF {
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

  public static class c_m054 implements ConstraintIF {
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

  public static class c_m055 implements ConstraintIF {
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

  public static class c_m056 implements ConstraintIF {
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

  public static class c_m057 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_LFSD = features.get("LFSD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_LFSD = features.get("LFSD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_LFW = features.get("LFW").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_LFW = features.get("LFW").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("ROUND") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("30") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("ROUND") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("40") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("ROUND") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("50") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("ROUND") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("30") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("ROUND") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("40") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("ROUND") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("50") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("FLAT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("30") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("FLAT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("40") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("FLAT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("50") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("FLAT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("30") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("FLAT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("40") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSETYPE = features.get("HOSETYPE").getString();
      String f_INSLHOSE = features.get("INSLHOSE").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSETYPE.compareTo("FLAT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_INSLHOSE.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("50") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
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

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("1") == 0)) ) {
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

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("2") == 0)) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m078 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m079 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m080 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m081 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m082 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m083 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m084 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

  public static class c_m085 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m086 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m087 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m088 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m089 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m090 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m091 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m092 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

  public static class c_m093 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TRAILER = features.get("TRAILER").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("ACKERMAN") == 0)) ) {
        s_validate = 0;
      }
      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_SLDTIRE = features.get("SLDTIRE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("FIFTH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SLDTIRE.compareTo("Y") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_SLDTIRE = features.get("SLDTIRE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("ACKERMAN") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SLDTIRE.compareTo("Y") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m097 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m101 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
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

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
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
      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_UNITYPE.compareTo("COOL") == 0)) || ((f_FUELHTR.compareTo("N") == 0)))) ) {
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
      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_UNITYPE.compareTo("COOL") == 0)) || ((f_FUELHTR.compareTo("N") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_UNITYPE.compareTo("COOL") == 0)) || ((f_FUELHTR.compareTo("N") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_UNITYPE.compareTo("COOL") == 0)) || ((f_FUELHTR.compareTo("N") == 0)))) ) {
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
      String f_TRAILER = features.get("TRAILER").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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
      String f_TRAILER = features.get("TRAILER").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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
      String f_TRAILER = features.get("TRAILER").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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
      String f_TRAILER = features.get("TRAILER").getString();
      String f_FUELHTR = features.get("FUELHTR").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FUELHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();

      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_BHTRVOLT = features.get("BHTRVOLT").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BHTRVOLT.compareTo("220") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ETHER = features.get("ETHER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ETHER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BTYPE.compareTo("FLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BTYPE.compareTo("NONFLASH") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_BTYPE = features.get("BTYPE").getString();
      String f_BCOLOR = features.get("BCOLOR").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_BTYPE.compareTo("ROTATE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_LFSD = features.get("LFSD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_LFSD = features.get("LFSD").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFSD.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_LFW = features.get("LFW").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("A") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_LFW = features.get("LFW").getString();
      String f_LFCOLOR = features.get("LFCOLOR").getString();
      String f_TRAILER = features.get("TRAILER").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_LFW.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LFCOLOR.compareTo("R") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_ENGINE.compareTo("CU24") == 0)) || ((f_ENGINE.compareTo("DU24") == 0)))) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("COOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("CU24") == 0)) ) {
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

      String f_TRAILER = features.get("TRAILER").getString();
      String f_UNITYPE = features.get("UNITYPE").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      if( !((f_TRAILER.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITYPE.compareTo("HEATCOOL") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_ENGINE.compareTo("DU24") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m150 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_ENGINE = features.get("ENGINE").getString();

      if( !((((((((f_ENGINE.compareTo("CU") == 0)) || ((f_ENGINE.compareTo("DU") == 0)))) || ((f_ENGINE.compareTo("IZ") == 0)))) || ((f_ENGINE.compareTo("JD") == 0)))) ) {
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
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_p001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_p002 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_p003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }
}
