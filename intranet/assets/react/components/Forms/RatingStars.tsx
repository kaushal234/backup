import React from "react";
import Rating from "@mui/material/Rating";
import "./RatingStars.css";

export function RatingStars({ input, meta, ...rest }: any) {
  const value = Number(input.value) || 0;

  const handleChange = (_event: any, newValue: any) => {
    input.onChange(newValue);
  };

  return (
    <div>
      <Rating
        value={value}
        onChange={handleChange}
        className="rating_stars__custom_rating"
        {...rest}
      />

      {meta.touched && meta.error && (
        <span style={{ color: "red", fontSize: 12 }}>{meta.error}</span>
      )}
    </div>
  );
}
