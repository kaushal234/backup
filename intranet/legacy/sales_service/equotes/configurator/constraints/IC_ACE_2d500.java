package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_ACE_2d500 extends ItemConstraints {

  public IC_ACE_2d500() {
    super();
  }

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_TRLRTYPE = features.get("TRLRTYPE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      s_display = 0;
      s_input = 0;
      f_TRLRTYPE = "";
      if( (((f_CAPACITY.compareTo("160") == 0)) || ((f_CAPACITY.compareTo("190") == 0))) ) {
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

      String f_FWSEP = features.get("FWSEP").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      s_display = 0;
      s_input = 0;
      f_FWSEP = "";
      if( (f_PRMMOVE.compareTo("D") == 0) ) {
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

  public static class c_f003 implements ConstraintIF {
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

  public static class c_f004 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      s_display = 0;
      s_input = 0;
      f_BLKHTR = "";
      if( (f_PRMMOVE.compareTo("D") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("BLKHTR").set(f_BLKHTR);
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

  public static class c_f006 implements ConstraintIF {
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

  public static class c_f007 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_COUPLER = features.get("COUPLER").getString();
      String f_AIRDELHS = features.get("AIRDELHS").getString();

      s_display = 0;
      s_input = 0;
      f_COUPLER = "";
      if( (f_AIRDELHS.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("COUPLER").set(f_COUPLER);
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

  public static class c_f009 implements ConstraintIF {
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

  public static class c_f010 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_UNITVOLT = features.get("UNITVOLT").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      s_display = 0;
      s_input = 0;
      f_UNITVOLT = "";
      if( (f_PRMMOVE.compareTo("E") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("UNITVOLT").set(f_UNITVOLT);
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

  public static class c_f012 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_BEACON = features.get("BEACON").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      s_display = 0;
      s_input = 0;
      f_BEACON = "";
      if( (f_PRMMOVE.compareTo("D") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("BEACON").set(f_BEACON);
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

  public static class c_m001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("ACKERMAN") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("ACKERMAN") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("FIFTH") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_TRLRTYPE = features.get("TRLRTYPE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRLRTYPE.compareTo("FIFTH") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
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

  public static class c_m012 implements ConstraintIF {
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

  public static class c_m013 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_BHTRVOLT = features.get("BHTRVOLT").getString();

      if( !((f_BLKHTR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BHTRVOLT.compareTo("110") == 0)) ) {
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

  public static class c_m015 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_AUXAIR = features.get("AUXAIR").getString();

      if( !((f_AUXAIR.compareTo("Y") == 0)) ) {
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

      String f_LOWTEMP = features.get("LOWTEMP").getString();

      if( !((f_LOWTEMP.compareTo("Y") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("30") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("ACE") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("40") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("ACE") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("50") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("ACE") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("60") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("ACE") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("30") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("KAISER") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("40") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("KAISER") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("50") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("KAISER") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("60") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("KAISER") == 0)) ) {
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

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("1") == 0)) ) {
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

      String f_PAINTSCH = features.get("PAINTSCH").getString();

      if( !((f_PAINTSCH.compareTo("2") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("208") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("240") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("380") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("480") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_LOWTEMP = features.get("LOWTEMP").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LOWTEMP.compareTo("N") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_LOWTEMP = features.get("LOWTEMP").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LOWTEMP.compareTo("N") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_LOWTEMP = features.get("LOWTEMP").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LOWTEMP.compareTo("Y") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_LOWTEMP = features.get("LOWTEMP").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_LOWTEMP.compareTo("Y") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("208") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("240") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("380") == 0)) ) {
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

      String f_PRMMOVE = features.get("PRMMOVE").getString();
      String f_UNITVOLT = features.get("UNITVOLT").getString();

      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_UNITVOLT.compareTo("480") == 0)) ) {
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

  public static class c_m064 implements ConstraintIF {
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

  public static class c_m065 implements ConstraintIF {
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

  public static class c_m066 implements ConstraintIF {
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

  public static class c_m067 implements ConstraintIF {
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

  public static class c_m068 implements ConstraintIF {
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

  public static class c_m069 implements ConstraintIF {
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

  public static class c_m070 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_LANG = features.get("LANG").getString();

      if( !((f_LANG.compareTo("ENG") == 0)) ) {
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

      String f_LANG = features.get("LANG").getString();

      if( !((f_LANG.compareTo("DAN") == 0)) ) {
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

      String f_LANG = features.get("LANG").getString();

      if( !((f_LANG.compareTo("FR") == 0)) ) {
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

      String f_LANG = features.get("LANG").getString();

      if( !((f_LANG.compareTo("ITL") == 0)) ) {
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

      String f_LANG = features.get("LANG").getString();

      if( !((f_LANG.compareTo("SPA") == 0)) ) {
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

      String f_LANG = features.get("LANG").getString();

      if( !((f_LANG.compareTo("SWE") == 0)) ) {
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

      String f_LANG = features.get("LANG").getString();

      if( !((f_LANG.compareTo("ZZZ") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("30") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("TLD") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("40") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("TLD") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("50") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("TLD") == 0)) ) {
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

      String f_AIRDELHS = features.get("AIRDELHS").getString();
      String f_HOSELGTH = features.get("HOSELGTH").getString();
      String f_COUPLER = features.get("COUPLER").getString();

      if( !((f_AIRDELHS.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_HOSELGTH.compareTo("60") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_COUPLER.compareTo("TLD") == 0)) ) {
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

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_AUXAIR = features.get("AUXAIR").getString();

      if( (f_AUXAIR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 90);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o002 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_AUXAIR = features.get("AUXAIR").getString();

      if( (f_AUXAIR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 90);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_AUXAIR = features.get("AUXAIR").getString();

      if( (f_AUXAIR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 90);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o004 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BEACON = features.get("BEACON").getString();
      String f_FWSEP = features.get("FWSEP").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_AUXAIR = features.get("AUXAIR").getString();

      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_FWSEP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BLKHTR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_AUXAIR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 90);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o005 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BEACON = features.get("BEACON").getString();
      String f_FWSEP = features.get("FWSEP").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_AUXAIR = features.get("AUXAIR").getString();

      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_FWSEP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BLKHTR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_AUXAIR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 90);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o006 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BEACON = features.get("BEACON").getString();
      String f_FWSEP = features.get("FWSEP").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();
      String f_AUXAIR = features.get("AUXAIR").getString();

      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_FWSEP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BLKHTR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_AUXAIR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 90);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o007 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o008 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o009 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o010 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BEACON = features.get("BEACON").getString();
      String f_FWSEP = features.get("FWSEP").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();

      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 120);
      }
      if( (f_FWSEP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BLKHTR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o011 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BEACON = features.get("BEACON").getString();
      String f_FWSEP = features.get("FWSEP").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();

      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 120);
      }
      if( (f_FWSEP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BLKHTR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o012 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BEACON = features.get("BEACON").getString();
      String f_FWSEP = features.get("FWSEP").getString();
      String f_BLKHTR = features.get("BLKHTR").getString();

      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 120);
      }
      if( (f_FWSEP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BLKHTR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o013 implements ConstraintIF {
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

  public static class c_o014 implements ConstraintIF {
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

  public static class c_o015 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o016 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o017 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("E") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o018 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("160") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o019 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("190") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_o020 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CAPACITY = features.get("CAPACITY").getString();
      String f_PRMMOVE = features.get("PRMMOVE").getString();

      if( !((f_CAPACITY.compareTo("230") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PRMMOVE.compareTo("D") == 0)) ) {
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
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m035 implements ConstraintIF {
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

  public static class c_m037 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m038 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }

  public static class c_m039 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }
}
