"use client";
import styles from "./page.module.css";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function Videos() {
  const [userName, setUserName] = useState("");
  const [successMessage, setSuccessMessage] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const [loading, setLoading] = useState(false);
  const [deleteVideoLoading, setDeleteVideoLoading] = useState(false);
  const [videos, setVideos] = useState([]);

  // logout
  const logOut = () => {
    localStorage.removeItem("vStreamToken");
    window.location.href = "/login";
  };
  // get user data function
  const getUserData = async () => {
    try {
      const apiUrl = "http://localhost:8000/api/user.php";
      const token = localStorage.getItem("vStreamToken");
      if (!token) {
        throw new Error("No token found");
      }
      const response = await fetch(apiUrl, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      });
      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.message);
      }
      if (result.user) {
        setUserName(result.user.username);
      } else {
        throw new Error("User data not found");
      }
    } catch (error) {
      console.error("Error fetching user data:", error);
      localStorage.removeItem("vStreamToken");
      window.location.href = "/login";
    }
  };

  // fetch videos
  const getUserVideos = async () => {
    try {
      setLoading(true);
      const apiUrl = "http://localhost:8000/api/my_videos.php";
      const token = localStorage.getItem("vStreamToken");
      if (!token) {
        throw new Error("No token found");
      }
      const response = await fetch(apiUrl, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      });

      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.message);
      }

      // check if videos exist necessary to avoid undefined error
      if (result.videos) {
        setVideos(result.videos);
      }
    } catch (error) {
      console.error("Error fetching user videos:", error);
      setErrorMessage("Failed to fetch videos. Please try again later.");
    } finally {
      setLoading(false);
    }
  };

  // del video
  const deleteVideo = async (videoId) => {
    try {
      setDeleteVideoLoading(true);
      const apiUrl = "http://localhost:8000/api/delete_video.php";
      const token = localStorage.getItem("vStreamToken");
      if (!token) {
        throw new Error("No token found");
      }
      const response = await fetch(apiUrl, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ videoId }),
      });

      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.message);
      }
      setSuccessMessage("Video deleted");
      setVideos((prevVideos) =>
        prevVideos.filter((video) => video.id !== videoId)
      );
    } catch (error) {
      console.error("Error deleting video:", error);
      setErrorMessage("Failed to delete video. Please try again later.");
    } finally {
      setDeleteVideoLoading(false);
    }
  };

  useEffect(() => {
    const token = localStorage.getItem("vStreamToken");
    if (!token) {
      window.location.href = "/login";
    } else {
      async function fetchData() {
        await getUserData();
        await getUserVideos();
      }
      fetchData();
    }
  }, []);

  // loading state
  if (loading) {
    return (
      <div className={styles.loadingContainer}>
        <div className={styles.loadingSpinner}></div>
        <p className={styles.loadingText}>Loading...</p>
      </div>
    );
  }
  // main content
  return (
    <div className={styles.container}>
      <div className={styles.sidebar}>
        <h2>vStream</h2>
        <nav className={styles.sidebarNav}>
          <Link href="/dashboard" className={`${styles.sidebarOption}`}>
            <div className={styles.sidebarOptionContent}>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                height="24px"
                viewBox="0 -960 960 960"
                width="24px"
                fill="#000000"
              >
                <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z" />
              </svg>
              My Profile
            </div>
          </Link>
          <Link
            href="/dashboard/videos"
            className={`${styles.sidebarOption} ${styles.active}`}
          >
            <div className={styles.sidebarOptionContent}>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                height="24px"
                viewBox="0 -960 960 960"
                width="24px"
                fill="#000000"
              >
                <path d="M320-200v-560l440 280-440 280Zm80-280Zm0 134 210-134-210-134v268Z" />
              </svg>
              My Videos
            </div>
          </Link>
          <Link href="/dashboard/upload" className={styles.sidebarOption}>
            <div className={styles.sidebarOptionContent}>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                height="24px"
                viewBox="0 -960 960 960"
                width="24px"
                fill="#000000"
              >
                <path d="M260-160q-91 0-155.5-63T40-377q0-78 47-139t123-78q25-92 100-149t170-57q117 0 198.5 81.5T760-520q69 8 114.5 59.5T920-340q0 75-52.5 127.5T740-160H260Zm0-80h480q42 0 71-29t29-71q0-42-29-71t-71-29h-60v-80q0-83-58.5-141.5T480-720q-83 0-141.5 58.5T280-520h-20q-58 0-99 41t-41 99q0 58 41 99t99 41Zm220-240Z" />
              </svg>
              Upload
            </div>
          </Link>
          <Link href="/dashboard/settings" className={styles.sidebarOption}>
            <div className={styles.sidebarOptionContent}>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                height="24px"
                viewBox="0 -960 960 960"
                width="24px"
                fill="#000000"
              >
                <path d="m370-80-16-128q-13-5-24.5-12T307-235l-119 50L78-375l103-78q-1-7-1-13.5v-27q0-6.5 1-13.5L78-585l110-190 119 50q11-8 23-15t24-12l16-128h220l16 128q13 5 24.5 12t22.5 15l119-50 110 190-103 78q1 7 1 13.5v27q0 6.5-2 13.5l103 78-110 190-118-50q-11 8-23 15t-24 12L590-80H370Zm70-80h79l14-106q31-8 57.5-23.5T639-327l99 41 39-68-86-65q5-14 7-29.5t2-31.5q0-16-2-31.5t-7-29.5l86-65-39-68-99 42q-22-23-48.5-38.5T533-694l-13-106h-79l-14 106q-31 8-57.5 23.5T321-633l-99-41-39 68 86 64q-5 15-7 30t-2 32q0 16 2 31t7 30l-86 65 39 68 99-42q22 23 48.5 38.5T427-266l13 106Zm42-180q58 0 99-41t41-99q0-58-41-99t-99-41q-59 0-99.5 41T342-480q0 58 40.5 99t99.5 41Zm-2-140Z" />
              </svg>
              Settings
            </div>
          </Link>
          <button
            onClick={logOut}
            className={`${styles.sidebarOption} ${styles.logout}`}
          >
            <div className={styles.sidebarOptionContent}>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                height="24px"
                viewBox="0 -960 960 960"
                width="24px"
                fill="#000000"
              >
                <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h280v80H200Zm440-160-55-58 102-102H360v-80h327L585-622l55-58 200 200-200 200Z" />
              </svg>
              Logout
            </div>
          </button>
        </nav>
      </div>
      <div className={styles.mainContent}>
        <header className={styles.header}>
          <span className={styles.welcomeMessage}>Welcome, {userName} ✨</span>
        </header>
        {/* Main dashboard content */}
        <div className={styles.contentArea}>
          <p className={styles.successMessage}>{successMessage}</p>
          <p className={styles.errorMessage}>{errorMessage}</p>
          {/* Videos list */}
          <h1>My Videos</h1>
          <div className={styles.videoList}>
            {videos.length === 0 ? (
              <div className={styles.noVideos}>No videos found.</div>
            ) : (
              <ul className={styles.list}>
                {videos.map((video) => (
                  <li key={video.id} className={styles.listItem}>
                    <div className={styles.videoInfo}>
                      <span className={styles.videoTitle}>
                        {video.video_name}
                      </span>
                    </div>
                    <div className={styles.videoActions}>
                      <Link
                        href={`/video/${video.id}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className={styles.viewButton}
                      >
                        View
                      </Link>
                      <button
                        className={styles.deleteButton}
                        onClick={() => deleteVideo(video.id)}
                        disabled={deleteVideoLoading}
                      >
                        {deleteVideoLoading ? "Deleting..." : "Delete"}
                      </button>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
