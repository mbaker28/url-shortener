import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  async rewrites () {
    return [
      {
        source: '/api/:path*',
        destination: process.env.PHP_URI || 'http://localhost:9000'
      }
    ];
  }
};

export default nextConfig;
