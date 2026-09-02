import React, { useEffect, useState } from "react";
import "./AppDrawer.css";
import Box from "@mui/material/Box";
import Drawer from "@mui/material/Drawer";
import List from "@mui/material/List";
import Divider from "@mui/material/Divider";
import ListItem from "@mui/material/ListItem";
import ListItemButton from "@mui/material/ListItemButton";
import ListItemIcon from "@mui/material/ListItemIcon";
import ListItemText from "@mui/material/ListItemText";
import { useTranslation } from "react-i18next";
import AppsIcon from "@mui/icons-material/Apps";
import { Avatar } from "@mui/material";
import { IDrawerMenuItem } from "../../@type/IDrawerMenuItem";
import { useAppSelector } from "../../hooks/hooks";
import { getProfilePicture } from "../../api/getProfilePicture";

interface IProps {
  isOpen: boolean;
  setIsOpen: (value: boolean) => void;
  menuItems: Array<IDrawerMenuItem>;
  selectedItem: IDrawerMenuItem;
  setSelectedItem: (item: IDrawerMenuItem) => void;
}

export default function AppDrawer(props: IProps) {
  const userInfo = useAppSelector((state) => state.auth.userInfo);
  const { isOpen, setIsOpen, menuItems, selectedItem, setSelectedItem } = props;
  const { t } = useTranslation();
  const [profilePhotoSrc, setProfilePhotoSrc] = useState("");

  const fetchProfilePhoto = async () => {
    if (userInfo?.photo && userInfo?.id) {
      const response = await getProfilePicture({
        peopleId: userInfo.id.toString(),
        photoId: userInfo.photo.replace("/people_files/", "").toString(),
      });
      if (response.data?.url) {
        setProfilePhotoSrc(response.data.url);
      }
    }
  };

  useEffect(() => {
    fetchProfilePhoto();
  }, []);

  return (
    <Drawer open={isOpen} onClose={() => setIsOpen(false)}>
      <Box
        data-cy="main-drawer"
        sx={{ width: 250 }}
        role="presentation"
        onClick={() => setIsOpen(false)}
      >
        <div
          className="app_drawer__profile"
          data-cy="main-drawer-profile-wrapper"
        >
          {profilePhotoSrc && (
            <Avatar src={profilePhotoSrc} data-cy="main-drawer-profile-image" />
          )}
          {!profilePhotoSrc && <Avatar data-cy="main-drawer-profile-image" />}
          {`${userInfo?.firstname ?? ""} ${userInfo?.lastname ?? ""}`}
        </div>
        <Divider />
        <List>
          {menuItems.map((menuItem) => (
            <ListItem key={menuItem.title} disablePadding>
              <ListItemButton
                className="app_drawer_button"
                selected={menuItem.title === selectedItem.title}
                onClick={() => setSelectedItem(menuItem)}
              >
                <ListItemIcon>
                  <AppsIcon className="app_drawer_button" />
                </ListItemIcon>
                <ListItemText
                  data-cy={`drawer-${menuItem.dataCy}`}
                  primary={t(menuItem.title)}
                />
              </ListItemButton>
            </ListItem>
          ))}
        </List>
      </Box>
    </Drawer>
  );
}
