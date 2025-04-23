"use client";
import Link from "next/link";
import Image from "next/image";
import styles from "./page.module.css";
import dashboardImage from "../../public/dashboard.png";
import uploadIcon from "../../public/upload.png";
import shieldIcon from "../../public/shield.png";
import optionsIcon from "../../public/options.png";

export default function Home() {
  return (
    <div className={styles.wrapper}>
      {/* Header */}
      <header className={styles.header}>
        <div className={styles.logo}>vStream 🎬</div>
        <nav className={styles.nav}>
          <Link href="/login" className={styles.navButton}>
            Login
          </Link>
          <Link
            href="/register"
            className={`${styles.navButton} ${styles.register}`}
          >
            Sign Up
          </Link>
        </nav>
      </header>

      {/* Hero Section */}
      <main className={styles.hero}>
        <h1 className={styles.title}>Stream Smarter with vStream</h1>
        <p className={styles.subtitle}>
          Organize, upload, and enjoy your videos — built for simplicity, speed,
          and style.
        </p>
      </main>

      {/* Features */}
      <h2 className={styles.sectionTitle}>Why Choose vStream?</h2>
      <section className={styles.features}>
        <div className={styles.feature}>
          <Image src={uploadIcon} alt="Upload Icon" width={48} height={48} />
          <h3>Easy Uploads</h3>
          <p>
            Drag and drop your videos or upload in one click. Simple, fast, and
            secure.
          </p>
        </div>
        <div className={styles.feature}>
          <Image src={optionsIcon} alt="Profile Icon" width={48} height={48} />
          <h3>Personal Dashboard</h3>
          <p>
            Manage your content and settings in a clean, user-friendly
            interface.
          </p>
        </div>
        <div className={styles.feature}>
          <Image src={shieldIcon} alt="Security Icon" width={48} height={48} />
          <h3>Private & Secure</h3>
          <p>
            Your videos are yours. End-to-end encrypted and private by default.
          </p>
        </div>
      </section>

      {/* Screenshot Section */}
      <section className={styles.screenshot}>
        <Image
          src={dashboardImage}
          alt="vStream Dashboard Preview"
          width={800}
          height={450}
          className={styles.screenshotImage}
        />
      </section>

      {/* Footer */}
      <footer className={styles.footer}>
        <p>&copy; 2025 vStream. All rights reserved.</p>
      </footer>
    </div>
  );
}
