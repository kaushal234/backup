import React from "react";
import "./ProfileCard.css";
import { useTranslation } from "react-i18next";
import { Avatar, Link, Paper, Typography } from "@mui/material";
import Ellipsis from "../Ellipsis/Ellipsis";

interface IProps {
  fullName?: string;
  profilePhotoSrc?: string;
  businessUnitName?: string;
  departmentName?: string;
  positionCategory?: string | null;
  email?: string;
  premiseName?: string;
  phoneNumbers?: Array<string>;
}

function ProfileCard(props: IProps) {
  const { t } = useTranslation();
  const {
    fullName,
    profilePhotoSrc,
    businessUnitName,
    departmentName,
    positionCategory,
    email,
    premiseName,
    phoneNumbers,
  } = props;

  if (!fullName) return <div />;

  return (
    <Paper className="profile__card">
      <div className="profile__image_wrapper">
        {!profilePhotoSrc && <Avatar data-cy="profile-avatar" />}
        {profilePhotoSrc && (
          <Avatar
            data-cy="profile-avatar"
            className="profile__avatar_image"
            src={profilePhotoSrc}
          />
        )}
      </div>
      <div className="profile__name_wrapper">
        <Typography data-cy="profile-full-name" variant="h5" gutterBottom>
          {fullName}
        </Typography>
      </div>
      <div className="profile__details">
        <div className="profile__details_row">
          <div className="profile__details_key">
            <Typography
              className="cui_light_text"
              variant="body1"
              gutterBottom
              data-cy="profile-info-label"
            >
              {t("profile_details.fields.business_unit")}
            </Typography>
          </div>
          <div className="profile__details_value">
            <Typography
              variant="body1"
              gutterBottom
              data-cy="profile-info-value"
            >
              {businessUnitName ?? "---"}
            </Typography>
          </div>
        </div>
        <div className="profile__details_row">
          <div className="profile__details_key">
            <Typography
              className="cui_light_text"
              variant="body1"
              gutterBottom
              data-cy="profile-info-label"
            >
              {t("profile_details.fields.department")}
            </Typography>
          </div>
          <div className="profile__details_value">
            <Typography
              variant="body1"
              gutterBottom
              data-cy="profile-info-value"
            >
              {departmentName ?? "---"}
            </Typography>
          </div>
        </div>
        <div className="profile__details_row">
          <div className="profile__details_key">
            <Typography
              className="cui_light_text"
              variant="body1"
              gutterBottom
              data-cy="profile-info-label"
            >
              {t("profile_details.fields.position")}
            </Typography>
          </div>
          <div className="profile__details_value">
            <Typography
              variant="body1"
              gutterBottom
              data-cy="profile-info-value"
            >
              {positionCategory ?? "---"}
            </Typography>
          </div>
        </div>
        <div className="profile__details_row">
          <div className="profile__details_key">
            <Typography
              className="cui_light_text"
              variant="body1"
              gutterBottom
              data-cy="profile-info-label"
            >
              {t("profile_details.fields.email")}
            </Typography>
          </div>
          <div className="profile__details_value">
            {email ? (
              <Typography
                variant="body1"
                gutterBottom
                data-cy="profile-info-value"
              >
                <Link href={`mailto:${email}`}>
                  <Ellipsis letterSpacing={1.8}>{email}</Ellipsis>
                </Link>
              </Typography>
            ) : (
              "---"
            )}
          </div>
        </div>
        <div className="profile__details_row">
          <div className="profile__details_key">
            <Typography
              className="cui_light_text"
              variant="body1"
              gutterBottom
              data-cy="profile-info-label"
            >
              {t("profile_details.fields.premise")}
            </Typography>
          </div>
          <div className="profile__details_value">
            <Typography
              variant="body1"
              gutterBottom
              data-cy="profile-info-value"
            >
              {premiseName ?? "---"}
            </Typography>
          </div>
        </div>
        <div className="profile__details_row">
          <div className="profile__details_key">
            <Typography
              className="cui_light_text"
              variant="body1"
              gutterBottom
              data-cy="profile-info-label"
            >
              {t("profile_details.fields.phones")}
            </Typography>
          </div>
          <div className="profile__details_value">
            <Typography
              className="profile__phones"
              variant="body1"
              gutterBottom
              data-cy="profile-info-value"
            >
              {!phoneNumbers?.length && "---"}
              {phoneNumbers?.map((phoneNumber) => (
                <Link href={`tel:${phoneNumber}`} key={phoneNumber}>
                  {phoneNumber}
                </Link>
              ))}
            </Typography>
          </div>
        </div>
      </div>
    </Paper>
  );
}

export default ProfileCard;
