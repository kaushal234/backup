import React, { ReactNode, useEffect, useRef, useState } from "react";
import AppBar from "@mui/material/AppBar";
import DehazeIcon from "@mui/icons-material/Dehaze";
import "./HomeAppBar.css";
import {
  Avatar,
  ButtonBase,
  Card,
  Collapse,
  Divider,
  IconButton,
} from "@mui/material";
import { useLocation, useNavigate } from "react-router";
import ProfileMenu from "../ProfileMenu/ProfileMenu";
import AppDrawer from "../AppDrawer/AppDrawer";
import { IDrawerMenuItem } from "../../@type/IDrawerMenuItem";
import Logo from "../Logo/Logo";
import { useAppSelector } from "../../hooks/hooks";
import BreadcrumbBar from "../BreadcrumbBar/BreadcrumbBar";
import NetworkStatusBar from "../NetworkStatusBar/NetworkStatusBar";
import GlobalTranslate from "../GlobalTranslate/GlobalTranslate";
import { ROUTES } from "../../constants/routes";

interface IProps {
  children: ReactNode;
  noCard?: boolean;
  isFullScreen?: boolean;
}

export default function HomeAppBar(props: IProps) {
  const isDrawerOpenGlobal = useAppSelector(
    (state) => state.appBar.isDrawerOpen
  );
  const { children, noCard, isFullScreen } = props;
  const { menuItems, selectedItem } = useAppSelector((state) => state.appBar);
  const breadcrumbs = useAppSelector((state) => state.breadcrumb.data);
  const navigate = useNavigate();
  const [isDrawerOpen, setIsDrawerOpen] = useState(false);
  const headerPadRef = useRef<HTMLDivElement>(null);
  const { pathname } = useLocation();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const handleSelect = (item: IDrawerMenuItem) => {
    navigate(item.path);
  };

  const handleResize = () => {
    const header = document.getElementsByClassName("home_app_bar__sub_wrapper");
    if (header[0] && headerPadRef.current) {
      const headerHeight = header[0].getBoundingClientRect().height;
      headerPadRef.current.style.height = `${headerHeight}px`;
    }
  };

  const handleLogoClick = () => {
    navigate(ROUTES.home);
  };

  useEffect(() => {
    window.addEventListener("resize", handleResize);
    return () => {
      window.removeEventListener("resize", handleResize);
    };
  }, []);

  useEffect(() => {
    handleResize();
  }, [headerPadRef.current, breadcrumbs, pathname]);

  useEffect(() => {
    if (isDrawerOpenGlobal) {
      setIsDrawerOpen(isDrawerOpenGlobal);
    }
  }, [isDrawerOpenGlobal]);

  return (
    <>
      <AppBar className="home_app_bar__wrapper" position="fixed">
        <div className="home_app_bar__sub_wrapper">
          <div className="home_app_bar_row">
            <div className="home_app_bar__section">
              <IconButton
                className="cui_icon_button_no_pad"
                onClick={() => setIsDrawerOpen((prev) => !prev)}
                data-cy="home-app-bar-menu"
              >
                <Avatar className="home_app_bar_avatar_menu">
                  <DehazeIcon className="home_app_bar_menu" />
                </Avatar>
              </IconButton>
            </div>
            <div className="home_app_bar__section">
              <ButtonBase onClick={handleLogoClick}>
                <Logo className="home_app_bar__logo" />
              </ButtonBase>
            </div>
            <div className="home_app_bar__section home_app_bar__section_right">
              <GlobalTranslate />
              <ProfileMenu />
            </div>
          </div>
          <Divider />
          <BreadcrumbBar />
        </div>
        <NetworkStatusBar />
      </AppBar>
      <div className="home_app_bar__pad" ref={headerPadRef} />
      <Collapse in={!isOnline}>
        <div className="home_app_bar__network_bar_pad" />
      </Collapse>
      <AppDrawer
        isOpen={isDrawerOpen}
        setIsOpen={setIsDrawerOpen}
        menuItems={menuItems}
        selectedItem={selectedItem}
        setSelectedItem={handleSelect}
      />

      <div className="home_app_bar__content_root_wrapper">
        {noCard && (
          <div
            className={`home_app_bar__content_wrapper--main ${
              breadcrumbs.length
                ? "home_app_bar__content_wrapper--breadcrumb"
                : "home_app_bar__content_wrapper--no_breadcrumb"
            } ${!isFullScreen && "home_app_bar__content_wrapper--container"}`}
          >
            {children}
          </div>
        )}
        {!noCard && (
          <div className="home_app_bar__content_wrapper--no_card">
            <Card
              variant="outlined"
              className={`home_app_bar__content_card ${
                !isFullScreen && "home_app_bar__content_card_container"
              }`}
            >
              {children}
            </Card>
          </div>
        )}
      </div>
    </>
  );
}
