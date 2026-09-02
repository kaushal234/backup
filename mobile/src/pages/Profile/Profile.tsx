import React, { useEffect, useState } from "react";
import "./Profile.css";
import { useParams } from "react-router";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { setLastBreadcrumbAppendString } from "../../redux/slices/breadcrumbSlice";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { getPeopleById } from "../../api/getPeopleById";
import { setProfileDetail } from "../../redux/slices/profileDetailSlice";
import { getProfilePicture } from "../../api/getProfilePicture";
import { toastError } from "../../utils/api";
import ProfileCard from "../../components/ProfileCard/ProfileCard";

const breadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.home", link: ROUTES.home },
  { title: "breadcrumb.profile", link: "" },
];

function Profile() {
  useDrawer(ROUTES.home);
  useBreadcrumbs(breadcrumbs);
  const dispatch = useAppDispatch();
  const { profileId } = useParams();
  const data = useAppSelector((state) => state.profileDetail.data);
  const [profilePhotoSrc, setProfilePhotoSrc] = useState("");

  const fetchPeople = async () => {
    dispatch(showMainLoader(true));
    const response = await getPeopleById({ id: profileId ?? "" });
    dispatch(showMainLoader(false));
    if (response.data) {
      dispatch(setProfileDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  const fetchProfilePic = async () => {
    let url = "";
    if (data?.photo) {
      const response = await getProfilePicture({
        peopleId: data.id?.toString() ?? "",
        photoId: data.photo.id.toString(),
      });
      url = response.data?.url ?? "";
    }
    setProfilePhotoSrc(url);
  };

  useEffect(() => {
    fetchPeople();
    dispatch(setLastBreadcrumbAppendString(`(#${profileId})`));
    return () => {
      dispatch(setProfileDetail(null));
    };
  }, [profileId]);

  useEffect(() => {
    fetchProfilePic();
  }, [data]);

  const fullName = `${data?.firstname ?? ""} ${data?.lastname ?? ""}`;

  const filteredPhones = (data?.phones ?? [])
    .filter((phone) => phone.type !== "reception" && phone.type !== "fax")
    .map((phone) => phone.number ?? "");

  if (!data) return <div />;

  return (
    <div className="profile__wrapper">
      <ProfileCard
        profilePhotoSrc={profilePhotoSrc}
        fullName={fullName}
        businessUnitName={data.businessUnit?.name}
        departmentName={data.department?.name}
        positionCategory={data.positionCategory}
        email={data.email}
        premiseName={data.premise?.name}
        phoneNumbers={filteredPhones}
      />
    </div>
  );
}

export default Profile;
