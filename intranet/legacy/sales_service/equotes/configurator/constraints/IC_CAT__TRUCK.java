package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_CAT__TRUCK extends ItemConstraints {

  public IC_CAT__TRUCK() {
    super();
  }

  public static class c_m001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CHASSIS = features.get("CHASSIS").getString();

      if( !((f_CHASSIS.compareTo("FVR34P") == 0)) ) {
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

      String f_CHASSIS = features.get("CHASSIS").getString();

      if( !((f_CHASSIS.compareTo("FTR33M") == 0)) ) {
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

      String f_BOX = features.get("BOX").getString();

      if( !((f_BOX.compareTo("1220") == 0)) ) {
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

      String f_BOX = features.get("BOX").getString();

      if( !((f_BOX.compareTo("1500") == 0)) ) {
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

      String f_BOX = features.get("BOX").getString();

      if( !((f_BOX.compareTo("2184") == 0)) ) {
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

      String f_AIRCON = features.get("AIRCON").getString();

      if( !((f_AIRCON.compareTo("THERMO") == 0)) ) {
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

      String f_AIRCON = features.get("AIRCON").getString();

      if( !((f_AIRCON.compareTo("CARRIER") == 0)) ) {
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

      String f_LOCK = features.get("LOCK").getString();

      if( !((f_LOCK.compareTo("TLD") == 0)) ) {
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

      String f_LOCK = features.get("LOCK").getString();

      if( !((f_LOCK.compareTo("PUR") == 0)) ) {
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

      String f_PLATFORM = features.get("PLATFORM").getString();

      if( !((f_PLATFORM.compareTo("2") == 0)) ) {
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

      String f_PLATFORM = features.get("PLATFORM").getString();

      if( !((f_PLATFORM.compareTo("4") == 0)) ) {
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

      String f_PLATFORM = features.get("PLATFORM").getString();

      if( !((f_PLATFORM.compareTo("FIX") == 0)) ) {
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

      String f_CHASSIS = features.get("CHASSIS").getString();
      String f_PLATFORM = features.get("PLATFORM").getString();

      if( !((f_CHASSIS.compareTo("FVR34P") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PLATFORM.compareTo("4") == 0)) ) {
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

      String f_CHASSIS = features.get("CHASSIS").getString();
      String f_PLATFORM = features.get("PLATFORM").getString();

      if( (((((f_CHASSIS.compareTo("FVR34P") == 0)) && ((f_PLATFORM.compareTo("2") == 0)))) || ((((f_CHASSIS.compareTo("FTR33M") == 0)) && ((f_PLATFORM.compareTo("4") == 0))))) ) {
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

  public static class c_m015 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
      int s_validate = globals.getInteger("validate");

      String f_CHASSIS = features.get("CHASSIS").getString();
      String f_PLATFORM = features.get("PLATFORM").getString();

      if( !((f_CHASSIS.compareTo("FTR33M") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_PLATFORM.compareTo("2") == 0)) ) {
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

      String f_CANOPY = features.get("CANOPY").getString();

      if( !((f_CANOPY.compareTo("Y") == 0)) ) {
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

      String f_DOOR = features.get("DOOR").getString();

      if( !((f_DOOR.compareTo("Y") == 0)) ) {
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

      String f_DCPUMP = features.get("DCPUMP").getString();

      if( !((f_DCPUMP.compareTo("Y") == 0)) ) {
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

      String f_STAIRS = features.get("STAIRS").getString();

      if( !((f_STAIRS.compareTo("Y") == 0)) ) {
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

      String f_CTLBOX = features.get("CTLBOX").getString();

      if( !((f_CTLBOX.compareTo("Y") == 0)) ) {
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
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_CANOPY = features.get("CANOPY").getString();
      String f_DOOR = features.get("DOOR").getString();
      String f_STAIRS = features.get("STAIRS").getString();

      if( (f_CANOPY.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 960);
      }
      if( (f_DOOR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 480);
      }
      if( (f_STAIRS.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 240);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o002 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_CTLBOX = features.get("CTLBOX").getString();

      if( (f_CTLBOX.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 1200);
      }

      globals.set("run_time", s_run_time);
    }
  }
}
