"use client";
import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import styles from "./page.module.css";
export default function Login() {
  const router = useRouter();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [loading, setLoading] = useState(false);
  const [userStatusLoading, setUserStatusLoading] = useState(true);

  // Check if user is already logged in
  useEffect(() => {
    const token = localStorage.getItem("vStreamToken");
    if (token) {
      router.push("/dashboard");
    } else {
      setUserStatusLoading(false);
    }
  }, [router]);

  const handleLoginSubmit = async (event) => {
    event.preventDefault();
    setError("");
    setSuccess("");
    setLoading(true);
    const apiUrl = "http://localhost:8000/api/login.php";

    try {
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          username,
          password,
        }),
      });
      const result = await response.json();
      if (!response.ok) {
        setError(`Error: ${result.message}`);
        return;
      }

      if (result.token) {
        localStorage.setItem("vStreamToken", result.token);
        setSuccess("Login successful! redirecting to dashboard...");
        setTimeout(() => {
          router.push("/dashboard");
        }, 2000); // redirect after 2 seconds
      } else {
        setError("Login successful but no token received.");
        return;
      }
    } catch (error) {
      console.error("Error during login:", error);
      setError("An error occurred. Please try again later.");
    } finally {
      setLoading(false);
    }
  };

  return userStatusLoading == true ? (
    <div className={styles.loading}>
      <p>Loading...</p>
    </div>
  ) : (
    <div className={styles.container}>
      <form className={styles.form} onSubmit={handleLoginSubmit}>
        {/* Display error/success messages */}
        {error && <p className={styles.error}>{error}</p>}
        {success && <p className={styles.success}>{success}</p>}
        {loading && <p className={styles.loading}>Loading...</p>}
        <h1>Login</h1>
        <div className={styles["input-group"]}>
          <label htmlFor="username">Username</label>
          <input
            type="text"
            id="username"
            onChange={(e) => {
              setUsername(e.target.value);
            }}
            placeholder="Enter username"
          />
        </div>
        <div className={styles["input-group"]}>
          <label htmlFor="password">Password</label>
          <input
            type="password"
            id="password"
            onChange={(e) => {
              setPassword(e.target.value);
            }}
            placeholder="Enter password"
          />
        </div>
        <button type="submit" className={styles.button} disabled={loading}>
          Login
        </button>
      </form>
    </div>
  );
}
