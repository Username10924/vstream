"use client";

import { useState, useEffect } from "react";
import { useParams } from "next/navigation";
import Plyr from "plyr-react";
import "plyr-react/plyr.css";
import styles from "./page.module.css";

export default function VideoPage() {
  const videoId = useParams().videoid;
  const [videoData, setVideoData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Fetch video
  useEffect(() => {
    const fetchVideoData = async () => {
      try {
        const response = await fetch(
          `http://localhost:8000/api/video.php?id=${videoId}`
        );
        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || "Failed to fetch video data");
        }

        if (result.video) {
          setVideoData(result.video);
        } else {
          throw new Error("Video not found");
        }
      } catch (err) {
        console.error(err);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchVideoData();
  }, [videoId]);

  if (loading) {
    return (
      <div className={styles.pageContainer}>
        <div className={styles.mainContent}>
          <div className={styles.loading}>Loading video...</div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className={styles.pageContainer}>
        <div className={styles.mainContent}>
          <div className={styles.error}>Error: {error}</div>
        </div>
      </div>
    );
  }

  if (!videoData) {
    return (
      <div className={styles.pageContainer}>
        <div className={styles.mainContent}>
          <div className={styles.error}>Video not found</div>
        </div>
      </div>
    );
  }

  const videoUrl = `http://localhost:8000/uploads/${videoData.file_name}`;
  const videoSource = {
    type: "video",
    sources: [
      {
        src: videoUrl,
      },
    ],
  };

  return (
    <div className={styles.pageContainer}>
      <div className={styles.mainContent}>
        <h1 className={styles.videoTitle}>{videoData.video_name}</h1>
        <div className={styles.videoPlayerContainer}>
          <Plyr source={videoSource} />
        </div>
      </div>
    </div>
  );
}
