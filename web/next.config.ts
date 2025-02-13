import type { NextConfig } from "next";
import { PHP_URI } from "@/app/lib/env";

const nextConfig: NextConfig = {
  async rewrites () {
    return [
      {
        source: '/api/:path*',
        destination: PHP_URI
      }
    ];
  }
};

export default nextConfig;
