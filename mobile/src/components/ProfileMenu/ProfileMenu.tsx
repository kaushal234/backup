import React from "react";
import { Logout } from "@mui/icons-material";
import AccountCircleOutlinedIcon from "@mui/icons-material/AccountCircleOutlined";
import { useNavigate } from "react-router";
import { useTranslation } from "react-i18next";
import SettingsOutlinedIcon from "@mui/icons-material/SettingsOutlined";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { logoutUser } from "../../utils/auth";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import AvatarMenu from "../AvatarMenu/AvatarMenu";
import { ROUTES } from "../../constants/routes";
import useThemeSelector from "../../hooks/useThemeSelector";
import useLanguageSelector from "../../hooks/useLanguageSelector";

export default function ProfileMenu() {
  const { t } = useTranslation();
  const themeMenuItems = useThemeSelector();
  const languageMenuItems = useLanguageSelector();
  const userInfo = useAppSelector((state) => state.auth.userInfo);

  const navigate = useNavigate();
  const dispatch = useAppDispatch();

  const handleLogout = async () => {
    await logoutUser();
    dispatch(setToastMessage("logout_success"));
    navigate(ROUTES.login);
  };

  const handleProfileClick = () => {
    navigate(`${ROUTES.profile}/${userInfo?.id?.toString() ?? ""}`);
  };

  const handleSettingClick = () => {
    navigate(ROUTES.settings);
  };

  return (
    <AvatarMenu
      dataCy="profile-menu"
      icon={<AccountCircleOutlinedIcon />}
      menuItems={[
        {
          type: "IMenuItem",
          text: "profile.my_profile",
          icon: <AccountCircleOutlinedIcon fontSize="small" />,
          onClick: handleProfileClick,
          dataCy: "profile-menu-profile",
        },
        { type: "IMenuDivider" },
        { type: "IMenuTitle", text: "profile.theme" },
        ...themeMenuItems,
        { type: "IMenuDivider" },
        { type: "IMenuTitle", text: `${t("profile.language")} 🇺🇳` },
        ...languageMenuItems,
        { type: "IMenuDivider" },
        {
          type: "IMenuItem",
          text: "profile.settings",
          icon: <SettingsOutlinedIcon fontSize="small" />,
          onClick: handleSettingClick,
          dataCy: "profile-menu-settings",
        },
        { type: "IMenuDivider" },
        {
          type: "IMenuItem",
          text: "profile.logout",
          icon: <Logout fontSize="small" />,
          onClick: handleLogout,
          dataCy: "profile-menu-logout",
        },
      ]}
    />
  );
}
