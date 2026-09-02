package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_NBL extends ItemConstraints {

  public IC_NBL() {
    super();
  }

  public static class c_o001 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BOOM_OPT = features.get("BOOM_OPT").getString();
      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();

      if( (((f_BOOM_OPT.compareTo("CAN") == 0)) || ((f_BOOM_OPT.compareTo("CC") == 0))) ) {
        s_run_time = (s_run_time + 960);
      }
      if( (((((f_BOOM_OPT.compareTo("CT") == 0)) || ((f_BOOM_OPT.compareTo("FC") == 0)))) || ((f_BOOM_OPT.compareTo("TC") == 0))) ) {
        s_run_time = (s_run_time + 480);
      }
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 840);
      }
      if( (f_CABDOOR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 960);
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

      String f_BOOM_OPT = features.get("BOOM_OPT").getString();
      String f_LIFTSPD = features.get("LIFTSPD").getString();
      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_SEATBELT = features.get("SEATBELT").getString();
      String f_STABLZR = features.get("STABLZR").getString();
      String f_DECKPROT = features.get("DECKPROT").getString();
      String f_WOODBUMP = features.get("WOODBUMP").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( (f_BOOM_OPT.compareTo("CAN") == 0) ) {
        s_run_time = (s_run_time + 480);
      }
      if( (((((f_BOOM_OPT.compareTo("CC") == 0)) || ((f_BOOM_OPT.compareTo("CT") == 0)))) || ((f_BOOM_OPT.compareTo("FT") == 0))) ) {
        s_run_time = (s_run_time + 300);
      }
      if( (f_BOOM_OPT.compareTo("FC") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BOOM_OPT.compareTo("TC") == 0) ) {
        s_run_time = (s_run_time + 90);
      }
      if( (f_BOOM_OPT.compareTo("TT") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_LIFTSPD.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 840);
      }
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 360);
      }
      if( (f_CABDOOR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 240);
      }
      if( (f_SEATBELT.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_STABLZR.compareTo("STAB") == 0) ) {
        s_run_time = (s_run_time + 360);
      }
      if( (f_STABLZR.compareTo("MAN") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_DECKPROT.compareTo("ALUM") == 0) ) {
        s_run_time = (s_run_time + 300);
      }
      if( (f_DECKPROT.compareTo("TANK") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_WOODBUMP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 180);
      }
      if( (f_THROTTLE.compareTo("MAN") == 0) ) {
        s_run_time = (s_run_time + 120);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o003 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();
      String f_STABLZR = features.get("STABLZR").getString();
      String f_LFCTLBOX = features.get("LFCTLBOX").getString();
      String f_LRCTLBOX = features.get("LRCTLBOX").getString();
      String f_WORKLITE = features.get("WORKLITE").getString();
      String f_PARKBRK = features.get("PARKBRK").getString();

      if( (f_AIRFAN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (((f_CABHEAT.compareTo("DEVICE") == 0)) || ((f_CABHEAT.compareTo("ELEC") == 0))) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_STABLZR.compareTo("STAB") == 0) ) {
        s_run_time = (s_run_time + 120);
      }
      if( (((f_LFCTLBOX.compareTo("EMER") == 0)) || ((f_LFCTLBOX.compareTo("FIX") == 0))) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_LRCTLBOX.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_WORKLITE.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_PARKBRK.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_TRANS = features.get("TRANS").getString();
      String f_ENGINE = features.get("ENGINE").getString();

      s_display = 0;
      s_input = 0;
      f_TRANS = "";
      if( (f_ENGINE.compareTo("IZ") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("TRANS").set(f_TRANS);
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

  public static class c_f003 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_CABIN = features.get("CABIN").getString();

      s_display = 0;
      s_input = 0;
      f_WINDSCRN = "";
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("WINDSCRN").set(f_WINDSCRN);
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

      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABIN = features.get("CABIN").getString();

      s_display = 0;
      s_input = 0;
      f_AIRFAN = "";
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("AIRFAN").set(f_AIRFAN);
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

      String f_CABHEAT = features.get("CABHEAT").getString();
      String f_CABIN = features.get("CABIN").getString();

      s_display = 0;
      s_input = 0;
      f_CABHEAT = "";
      if( (f_CABIN.compareTo("Y") == 0) ) {
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

  public static class c_f006 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_BEACONSW = features.get("BEACONSW").getString();
      String f_BEACON = features.get("BEACON").getString();

      s_display = 0;
      s_input = 0;
      f_BEACONSW = "";
      if( (f_BEACON.compareTo("Y") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("BEACONSW").set(f_BEACONSW);
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRANS = features.get("TRANS").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("Z") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("MAN") == 0)) ) {
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
      String f_TRANS = features.get("TRANS").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("P") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("MAN") == 0)) ) {
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
      String f_TRANS = features.get("TRANS").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("Z") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("FOOT") == 0)) ) {
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
      String f_TRANS = features.get("TRANS").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("P") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("FOOT") == 0)) ) {
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
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("MAN") == 0)) ) {
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
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("FOOT") == 0)) ) {
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

      String f_FUELTANK = features.get("FUELTANK").getString();

      if( !((f_FUELTANK.compareTo("45") == 0)) ) {
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

      String f_FUELTANK = features.get("FUELTANK").getString();

      if( !((f_FUELTANK.compareTo("65") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("CAN") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("FT") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("FC") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("TT") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("TC") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("CT") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("CC") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("8") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("NONE") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("CAN") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("FT") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("FC") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("TT") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("TC") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("CT") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("CC") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("NONE") == 0)) ) {
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

      String f_BOOMBUMP = features.get("BOOMBUMP").getString();

      if( !((f_BOOMBUMP.compareTo("90") == 0)) ) {
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

      String f_BOOMBUMP = features.get("BOOMBUMP").getString();

      if( !((f_BOOMBUMP.compareTo("145") == 0)) ) {
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

      String f_SEATBELT = features.get("SEATBELT").getString();
      String f_SSTSEAT = features.get("SSTSEAT").getString();

      if( !((f_SEATBELT.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SSTSEAT.compareTo("N") == 0)) ) {
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

      String f_SEATBELT = features.get("SEATBELT").getString();
      String f_SSTSEAT = features.get("SSTSEAT").getString();

      if( !((f_SEATBELT.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_SSTSEAT.compareTo("N") == 0)) ) {
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

      String f_LIFTSPD = features.get("LIFTSPD").getString();

      if( !((f_LIFTSPD.compareTo("Y") == 0)) ) {
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

      String f_LIFTSPD = features.get("LIFTSPD").getString();

      if( !((f_LIFTSPD.compareTo("N") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();

      if( !((f_CABIN.compareTo("N") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("ELEC") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("DEVICE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("NONE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("ELEC") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("ELEC") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("ELEC") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("DEVICE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("N") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("DEVICE") == 0)) ) {
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

      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();

      if( !((f_CABIN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABDOOR.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_WINDSCRN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_AIRFAN.compareTo("Y") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABHEAT.compareTo("DEVICE") == 0)) ) {
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

      String f_STABLZR = features.get("STABLZR").getString();

      if( !((f_STABLZR.compareTo("STAB") == 0)) ) {
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

      String f_STABLZR = features.get("STABLZR").getString();

      if( !((f_STABLZR.compareTo("MAN") == 0)) ) {
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

      String f_STABLZR = features.get("STABLZR").getString();

      if( !((f_STABLZR.compareTo("NONE") == 0)) ) {
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

      String f_DECKPROT = features.get("DECKPROT").getString();

      if( !((f_DECKPROT.compareTo("ALUM") == 0)) ) {
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

      String f_DECKPROT = features.get("DECKPROT").getString();

      if( !((f_DECKPROT.compareTo("TANK") == 0)) ) {
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

      String f_LFCTLBOX = features.get("LFCTLBOX").getString();

      if( !((f_LFCTLBOX.compareTo("FIX") == 0)) ) {
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

      String f_LFCTLBOX = features.get("LFCTLBOX").getString();

      if( !((f_LFCTLBOX.compareTo("EMER") == 0)) ) {
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

      String f_LRCTLBOX = features.get("LRCTLBOX").getString();

      if( !((f_LRCTLBOX.compareTo("FIX") == 0)) ) {
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

      String f_WORKLITE = features.get("WORKLITE").getString();

      if( !((f_WORKLITE.compareTo("Y") == 0)) ) {
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

      String f_PARKBRK = features.get("PARKBRK").getString();

      if( !((f_PARKBRK.compareTo("Y") == 0)) ) {
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

      String f_WOODBUMP = features.get("WOODBUMP").getString();

      if( !((f_WOODBUMP.compareTo("Y") == 0)) ) {
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

      String f_FIREXTNG = features.get("FIREXTNG").getString();

      if( !((f_FIREXTNG.compareTo("Y") == 0)) ) {
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

      String f_SCOTCH = features.get("SCOTCH").getString();

      if( !((f_SCOTCH.compareTo("Y") == 0)) ) {
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

      String f_LRCTLBOX = features.get("LRCTLBOX").getString();

      if( !((f_LRCTLBOX.compareTo("EMER") == 0)) ) {
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

      String f_RFCTLBOX = features.get("RFCTLBOX").getString();

      if( !((f_RFCTLBOX.compareTo("FIX") == 0)) ) {
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

      String f_RFCTLBOX = features.get("RFCTLBOX").getString();

      if( !((f_RFCTLBOX.compareTo("MOV") == 0)) ) {
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

      String f_PARKLOCK = features.get("PARKLOCK").getString();

      if( !((f_PARKLOCK.compareTo("Y") == 0)) ) {
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

      String f_FIREBRKT = features.get("FIREBRKT").getString();

      if( !((f_FIREBRKT.compareTo("Y") == 0)) ) {
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
      String f_CABIN = features.get("CABIN").getString();

      if( !((f_BEACON.compareTo("AF") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABIN.compareTo("N") == 0)) ) {
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
      String f_CABIN = features.get("CABIN").getString();

      if( !((f_BEACON.compareTo("AF") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABIN.compareTo("Y") == 0)) ) {
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
      String f_CABIN = features.get("CABIN").getString();

      if( !((f_BEACON.compareTo("AR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABIN.compareTo("N") == 0)) ) {
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
      String f_CABIN = features.get("CABIN").getString();

      if( !((f_BEACON.compareTo("AR") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_CABIN.compareTo("Y") == 0)) ) {
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

      String f_BEACONSW = features.get("BEACONSW").getString();

      if( !((f_BEACONSW.compareTo("Y") == 0)) ) {
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

      String f_EMERSTOP = features.get("EMERSTOP").getString();

      if( !((f_EMERSTOP.compareTo("Y") == 0)) ) {
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

      String f_LHMIRROR = features.get("LHMIRROR").getString();

      if( !((f_LHMIRROR.compareTo("Y") == 0)) ) {
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

      String f_RHMIRROR = features.get("RHMIRROR").getString();

      if( !((f_RHMIRROR.compareTo("Y") == 0)) ) {
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

      String f_STDTOOLS = features.get("STDTOOLS").getString();

      if( !((f_STDTOOLS.compareTo("Y") == 0)) ) {
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

      String f_BOOM = features.get("BOOM").getString();
      String f_BOOM_OPT = features.get("BOOM_OPT").getString();

      if( !((f_BOOM.compareTo("9") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_BOOM_OPT.compareTo("CE") == 0)) ) {
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

      String f_SSTSEAT = features.get("SSTSEAT").getString();

      if( !((f_SSTSEAT.compareTo("Y") == 0)) ) {
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

      String f_PAXSEAT = features.get("PAXSEAT").getString();

      if( !((f_PAXSEAT.compareTo("Y") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRANS = features.get("TRANS").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("P") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRANS = features.get("TRANS").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("P") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRANS = features.get("TRANS").getString();

      if( !((f_ENGINE.compareTo("PK") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("P") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRANS = features.get("TRANS").getString();

      if( !((f_ENGINE.compareTo("PK") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("P") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_TRANS = features.get("TRANS").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TRANS.compareTo("P") == 0)) ) {
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

      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_THROTTLE.compareTo("DR") == 0)) ) {
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

      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_THROTTLE.compareTo("RR") == 0)) ) {
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
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("FOOT") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("DU") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_THROTTLE.compareTo("DR") == 0)) || ((f_THROTTLE.compareTo("RR") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("PK") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("FOOT") == 0)) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("PK") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_THROTTLE.compareTo("DR") == 0)) || ((f_THROTTLE.compareTo("RR") == 0)))) ) {
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

      String f_ENGINE = features.get("ENGINE").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_THROTTLE.compareTo("FOOT") == 0)) ) {
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
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( !((f_ENGINE.compareTo("IZ") == 0)) ) {
        s_validate = 0;
      }
      if( !((((f_THROTTLE.compareTo("DR") == 0)) || ((f_THROTTLE.compareTo("RR") == 0)))) ) {
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

      String f_BOOM_OPT = features.get("BOOM_OPT").getString();
      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();

      if( (((f_BOOM_OPT.compareTo("CAN") == 0)) || ((f_BOOM_OPT.compareTo("CC") == 0))) ) {
        s_run_time = (s_run_time + 960);
      }
      if( (((((f_BOOM_OPT.compareTo("CT") == 0)) || ((f_BOOM_OPT.compareTo("FC") == 0)))) || ((f_BOOM_OPT.compareTo("TC") == 0))) ) {
        s_run_time = (s_run_time + 480);
      }
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 840);
      }
      if( (f_CABDOOR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 960);
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

      String f_BOOM_OPT = features.get("BOOM_OPT").getString();
      String f_LIFTSPD = features.get("LIFTSPD").getString();
      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_SEATBELT = features.get("SEATBELT").getString();
      String f_STABLZR = features.get("STABLZR").getString();
      String f_DECKPROT = features.get("DECKPROT").getString();
      String f_WOODBUMP = features.get("WOODBUMP").getString();
      String f_THROTTLE = features.get("THROTTLE").getString();

      if( (f_BOOM_OPT.compareTo("CAN") == 0) ) {
        s_run_time = (s_run_time + 480);
      }
      if( (((((f_BOOM_OPT.compareTo("CC") == 0)) || ((f_BOOM_OPT.compareTo("CT") == 0)))) || ((f_BOOM_OPT.compareTo("FT") == 0))) ) {
        s_run_time = (s_run_time + 300);
      }
      if( (f_BOOM_OPT.compareTo("FC") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_BOOM_OPT.compareTo("TC") == 0) ) {
        s_run_time = (s_run_time + 90);
      }
      if( (f_BOOM_OPT.compareTo("TT") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_LIFTSPD.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 840);
      }
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 360);
      }
      if( (f_CABDOOR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 240);
      }
      if( (f_SEATBELT.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_STABLZR.compareTo("STAB") == 0) ) {
        s_run_time = (s_run_time + 360);
      }
      if( (f_STABLZR.compareTo("MAN") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_DECKPROT.compareTo("ALUM") == 0) ) {
        s_run_time = (s_run_time + 300);
      }
      if( (f_DECKPROT.compareTo("TANK") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_WOODBUMP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 180);
      }
      if( (f_THROTTLE.compareTo("MAN") == 0) ) {
        s_run_time = (s_run_time + 120);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o005 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();
      String f_STABLZR = features.get("STABLZR").getString();
      String f_LFCTLBOX = features.get("LFCTLBOX").getString();
      String f_LRCTLBOX = features.get("LRCTLBOX").getString();
      String f_WORKLITE = features.get("WORKLITE").getString();
      String f_PARKBRK = features.get("PARKBRK").getString();

      if( (f_AIRFAN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (((f_CABHEAT.compareTo("DEVICE") == 0)) || ((f_CABHEAT.compareTo("ELEC") == 0))) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_STABLZR.compareTo("STAB") == 0) ) {
        s_run_time = (s_run_time + 120);
      }
      if( (((f_LFCTLBOX.compareTo("EMER") == 0)) || ((f_LFCTLBOX.compareTo("FIX") == 0))) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_LRCTLBOX.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_WORKLITE.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_PARKBRK.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o011 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
      int s_run_time = globals.getInteger("run_time");

      String f_BOOM_OPT = features.get("BOOM_OPT").getString();
      String f_LIFTSPD = features.get("LIFTSPD").getString();
      String f_CABIN = features.get("CABIN").getString();
      String f_CABDOOR = features.get("CABDOOR").getString();
      String f_WINDSCRN = features.get("WINDSCRN").getString();
      String f_AIRFAN = features.get("AIRFAN").getString();
      String f_CABHEAT = features.get("CABHEAT").getString();
      String f_SEATBELT = features.get("SEATBELT").getString();
      String f_STABLZR = features.get("STABLZR").getString();
      String f_DECKPROT = features.get("DECKPROT").getString();
      String f_LFCTLBOX = features.get("LFCTLBOX").getString();
      String f_LRCTLBOX = features.get("LRCTLBOX").getString();
      String f_WORKLITE = features.get("WORKLITE").getString();
      String f_PARKBRK = features.get("PARKBRK").getString();
      String f_WOODBUMP = features.get("WOODBUMP").getString();

      if( (f_BOOM_OPT.compareTo("CAN") == 0) ) {
        s_run_time = (s_run_time + 120);
      }
      if( (((((((((((f_BOOM_OPT.compareTo("CC") == 0)) || ((f_BOOM_OPT.compareTo("CT") == 0)))) || ((f_BOOM_OPT.compareTo("FC") == 0)))) || ((f_BOOM_OPT.compareTo("FT") == 0)))) || ((f_BOOM_OPT.compareTo("TC") == 0)))) || ((f_BOOM_OPT.compareTo("TT") == 0))) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_LIFTSPD.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_CABIN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_CABDOOR.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_WINDSCRN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_AIRFAN.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 12);
      }
      if( (((f_CABHEAT.compareTo("DEVICE") == 0)) || ((f_CABHEAT.compareTo("ELEC") == 0))) ) {
        s_run_time = (s_run_time + 12);
      }
      if( (f_SEATBELT.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_STABLZR.compareTo("STAB") == 0) ) {
        s_run_time = (s_run_time + 60);
      }
      if( (f_STABLZR.compareTo("MAN") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_DECKPROT.compareTo("ALUM") == 0) ) {
        s_run_time = (s_run_time + 30);
      }
      if( (f_DECKPROT.compareTo("TANK") == 0) ) {
        s_run_time = (s_run_time + 6);
      }
      if( (((f_LFCTLBOX.compareTo("EMER") == 0)) || ((f_LFCTLBOX.compareTo("FIX") == 0))) ) {
        s_run_time = (s_run_time + 12);
      }
      if( (f_LRCTLBOX.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 12);
      }
      if( (f_WORKLITE.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 12);
      }
      if( (f_PARKBRK.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 12);
      }
      if( (f_WOODBUMP.compareTo("Y") == 0) ) {
        s_run_time = (s_run_time + 6);
      }

      globals.set("run_time", s_run_time);
    }
  }

  public static class c_o008 implements ConstraintIF {
    public void beforeInput() {
    }

    public void validation() {
    }

    public void parameterSubstitution() {
    }
  }
}
