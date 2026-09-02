import React, { ReactNode } from "react";
import "./AvatarMenu.css";
import {
  Avatar,
  Divider,
  IconButton,
  ListItemIcon,
  Menu,
  MenuItem,
  Typography,
} from "@mui/material";
import { useTranslation } from "react-i18next";
import Check from "@mui/icons-material/Check";
import { contextMenuSlotProps } from "../../constants/styles";
import { IMenuComponent } from "../../@type/IMenuComponent";
import { generateRandomNumber } from "../../utils/utils";

interface IProps {
  icon: ReactNode;
  menuItems: Array<IMenuComponent>;
  dataCy?: string;
}

export default function AvatarMenu(props: IProps) {
  const { t } = useTranslation();
  const { icon, menuItems, dataCy = "" } = props;
  const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);
  const open = Boolean(anchorEl);

  const handleClick = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  return (
    <>
      <IconButton onClick={handleClick} className="cui_icon_button_no_pad">
        <Avatar data-cy={dataCy} className="avatar_menu__avatar">
          {icon}
        </Avatar>
      </IconButton>
      <Menu
        className="avatar_menu__context_menu"
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        onClick={handleClose}
        slotProps={contextMenuSlotProps}
        transformOrigin={{ horizontal: "right", vertical: "top" }}
        anchorOrigin={{ horizontal: "right", vertical: "bottom" }}
      >
        {menuItems.map((menuItem) => {
          if (menuItem.type === "IMenuDivider") {
            return <Divider key={`${generateRandomNumber()}`} />;
          }

          if (menuItem.type === "IMenuTitle") {
            return (
              <Typography
                className="avatar_menu__category_title"
                variant="overline"
                gutterBottom
                sx={{ display: "block" }}
                key={`${generateRandomNumber()}`}
              >
                {t(menuItem.text)}
              </Typography>
            );
          }

          return (
            <MenuItem
              key={menuItem.text}
              onClick={() => menuItem?.onClick?.(menuItem)}
              selected={menuItem.selected}
              data-cy={menuItem.dataCy}
            >
              {menuItem.icon && <ListItemIcon>{menuItem.icon}</ListItemIcon>}
              <div>{t(menuItem.text)}</div>
              <ListItemIcon
                className={`avatar_menu__selected ${
                  !menuItem.selected && "cui_invisible"
                }`}
              >
                <Check />
              </ListItemIcon>
            </MenuItem>
          );
        })}
      </Menu>
    </>
  );
}
