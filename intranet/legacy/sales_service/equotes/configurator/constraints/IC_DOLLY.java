package com.fullscope.configurator.constraints;

import com.fullscope.configurator.ItemConstraints;
import com.fullscope.configurator.ConstraintIF;
import com.fullscope.configurator.ConstrFunctions;

public class IC_DOLLY extends ItemConstraints {

  public IC_DOLLY() {
    super();
  }

  public static class c_f001 implements ConstraintIF {
    public void beforeInput() {
      int s_input = globals.getInteger("input");
      int s_display = globals.getInteger("display");

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( (f_DOLLTYPE.compareTo("BC6521U") == 0) ) {
        s_display = 0;
        s_input = 0;
        f_FINISH = "";
      }

      features.get("FINISH").set(f_FINISH);
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

      String f_TIRES = features.get("TIRES").getString();
      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      s_display = 0;
      s_input = 0;
      f_TIRES = "";
      if( (f_DOLLTYPE.compareTo("PD2014") == 0) ) {
        s_display = 1;
        s_input = 1;
      }

      features.get("TIRES").set(f_TIRES);
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC1010") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("BC6601A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("BC6601B") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6602") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD1010") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD2300") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("CD2300A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6146") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("CD6610") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("CD6610A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6620") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6646") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CR7730") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD1014") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();
      String f_TIRES = features.get("TIRES").getString();

      if( !((f_DOLLTYPE.compareTo("PD2014") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TIRES.compareTo("24") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("PD2014A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("PD2014C") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD2200") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("PD2200A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD6620") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("PD6620C") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("PD6620TG") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("SP8804A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("SP8805A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("SP8806A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("WS8801") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("WS8801A") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("WS8802") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("WS8803") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("WS8804") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("XHT0008") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();

      if( !((f_DOLLTYPE.compareTo("XHT00081") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6520") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6521") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC1010") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6601") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6601") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6602") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6603") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6603") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6520") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6521") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6522") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD1010") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD2300") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6146") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6610CE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6610CE") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6610K") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6610K") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6620") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD6646") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD9610") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CD9610") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD1014") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();
      String f_TIRES = features.get("TIRES").getString();

      if( !((f_DOLLTYPE.compareTo("PD2014") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TIRES.compareTo("16") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();
      String f_TIRES = features.get("TIRES").getString();

      if( !((f_DOLLTYPE.compareTo("PD2014") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TIRES.compareTo("16") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();
      String f_TIRES = features.get("TIRES").getString();

      if( !((f_DOLLTYPE.compareTo("PD2014") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_TIRES.compareTo("24") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD2200") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD6620") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD7710") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD7710") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CR7730") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CR7733") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("CR7733") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("BC6522") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD1010") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("PAINT") == 0)) ) {
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

      String f_DOLLTYPE = features.get("DOLLTYPE").getString();
      String f_FINISH = features.get("FINISH").getString();

      if( !((f_DOLLTYPE.compareTo("PD1010") == 0)) ) {
        s_validate = 0;
      }
      if( !((f_FINISH.compareTo("ZINC") == 0)) ) {
        s_validate = 0;
      }

      globals.set("validate", s_validate);
    }

    public void parameterSubstitution() {
    }
  }
}
