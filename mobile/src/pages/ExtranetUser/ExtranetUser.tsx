import React, { useEffect, useState } from "react";
import "./ExtranetUser.css";
import { useParams } from "react-router";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { setLastBreadcrumbAppendString } from "../../redux/slices/breadcrumbSlice";
import { useAppDispatch } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { toastError } from "../../utils/api";
import ProfileCard from "../../components/ProfileCard/ProfileCard";
import { getExtranetUserById } from "../../api/getExtranetUserById";
import { IExtranetUser } from "../../@type/IGetExtranetUserResponse";

const breadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.home", link: ROUTES.home },
  { title: "breadcrumb.extranet_user", link: "" },
];

function ExtranetUser() {
  useDrawer(ROUTES.home);
  useBreadcrumbs(breadcrumbs);
  const dispatch = useAppDispatch();
  const { profileId } = useParams();
  const [data, setData] = useState<IExtranetUser | null>(null);

  const fetchExtranetUser = async () => {
    dispatch(showMainLoader(true));
    const response = await getExtranetUserById({ id: profileId ?? "" });
    dispatch(showMainLoader(false));
    if (response.data) {
      setData(response.data);
    } else {
      toastError(dispatch, response);
    }
  };

  useEffect(() => {
    fetchExtranetUser();
    dispatch(setLastBreadcrumbAppendString(`(#${profileId})`));
    return () => {
      setData(null);
    };
  }, [profileId]);

  const fullName = `${data?.firstname ?? ""} ${data?.lastname ?? ""}`;

  const filteredPhones = (data?.phones ?? [])
    .filter((phone) => phone.type !== "reception" && phone.type !== "fax")
    .map((phone) => phone.number ?? "");

  if (!data) return <div />;

  return (
    <div className="extranet_user__wrapper">
      <ProfileCard
        fullName={fullName}
        email={data.email}
        phoneNumbers={filteredPhones}
      />
    </div>
  );
}

export default ExtranetUser;
